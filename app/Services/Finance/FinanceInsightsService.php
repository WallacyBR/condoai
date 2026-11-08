<?php

namespace App\Services\Finance;

use App\Models\Bank;
use App\Models\BudgetItem;
use App\Models\Condominium;
use App\Models\ExpenseRequest;
use App\Models\FinanceSettlement;
use App\Models\Payable;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinanceInsightsService
{
    public function dashboard(Condominium $condominium, Carbon $asOf, int $horizon): array
    {
        $payables = Payable::with('settlements')->where('condominium_id', $condominium->id)->get();
        $receivables = Receivable::with('settlements')->where('condominium_id', $condominium->id)->get();
        $availableBalance = (float) Bank::where('condominium_id', $condominium->id)->sum('balance');
        $periodEnd = $asOf->copy()->addDays($horizon)->endOfDay();

        $pendingPayables = $this->openTitles($payables);
        $pendingReceivables = $this->openTitles($receivables);
        $projectedReceipts = $pendingReceivables->filter(fn ($title) => $title->due_date->betweenIncluded($asOf, $periodEnd))->sum('open_amount');
        $projectedPayments = $pendingPayables->filter(fn ($title) => $title->due_date->betweenIncluded($asOf, $periodEnd))->sum('open_amount');
        $budget = $this->budgetStatus($condominium, $asOf);

        return [
            'as_of_date' => $asOf->toDateString(),
            'horizon_days' => $horizon,
            'cards' => [
                ['label' => 'Saldo bancário informado', 'value' => $availableBalance, 'tone' => 'blue'],
                ['label' => 'A receber em aberto', 'value' => (float) $pendingReceivables->sum('open_amount'), 'tone' => 'green'],
                ['label' => 'A pagar em aberto', 'value' => (float) $pendingPayables->sum('open_amount'), 'tone' => 'orange'],
                ['label' => 'Margem projetada', 'value' => $availableBalance + $projectedReceipts - $projectedPayments, 'tone' => 'cyan'],
                ['label' => 'Vencidos', 'value' => (float) ($pendingPayables->filter(fn ($title) => $title->due_date->lt($asOf))->sum('open_amount')), 'tone' => 'red'],
                ['label' => 'Solicitações pendentes', 'value' => ExpenseRequest::where('condominium_id', $condominium->id)->whereIn('status', ['submitted', 'in_approval'])->count(), 'tone' => 'purple'],
            ],
            'forecast' => $this->forecastSeries($availableBalance, $pendingReceivables, $pendingPayables, $asOf, $horizon),
            'budget' => $budget,
            'aging' => [
                'receivables' => $this->aging($pendingReceivables, $asOf),
                'payables' => $this->aging($pendingPayables, $asOf),
            ],
            'deviations' => $budget['deviations'],
        ];
    }

    public function dre(Condominium $condominium, Carbon $start, Carbon $end): array
    {
        $receivables = Receivable::with('account.parent')->where('condominium_id', $condominium->id)
            ->whereBetween('competence_date', [$start->toDateString(), $end->toDateString()])->get();
        $payables = Payable::with('account.parent')->where('condominium_id', $condominium->id)
            ->whereBetween('competence_date', [$start->toDateString(), $end->toDateString()])->get();

        $group = function (Collection $titles): array {
            return $titles->groupBy(fn ($title) => $title->account?->parent?->name ?: $title->account?->name ?: 'Sem classificação')
                ->map(fn (Collection $items, string $name) => [
                    'group' => $name,
                    'amount' => (float) $items->sum('amount'),
                    'items' => $items->groupBy(fn ($title) => $title->account?->name ?: 'Sem conta')->map(fn (Collection $accountTitles, string $account) => [
                        'account' => $account,
                        'amount' => (float) $accountTitles->sum('amount'),
                    ])->values()->all(),
                ])->values()->all();
        };

        $revenues = (float) $receivables->sum('amount');
        $expenses = (float) $payables->sum('amount');

        return [
            'title' => 'DRE Gerencial',
            'kind' => 'gerencial_competencia',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'revenues' => $group($receivables),
            'expenses' => $group($payables),
            'totals' => ['revenues' => $revenues, 'expenses' => $expenses, 'result' => $revenues - $expenses],
            'disclaimer' => 'Relatório gerencial por competência. Não substitui demonstrações contábeis formais.',
        ];
    }

    public function balancete(Condominium $condominium, Carbon $asOf): array
    {
        $settlements = FinanceSettlement::where('condominium_id', $condominium->id)->whereDate('settled_on', '<=', $asOf)->get();
        $payables = $this->openTitles(Payable::with('settlements')->where('condominium_id', $condominium->id)->whereDate('due_date', '<=', $asOf)->get());
        $receivables = $this->openTitles(Receivable::with('settlements')->where('condominium_id', $condominium->id)->whereDate('due_date', '<=', $asOf)->get());
        $reportedBalance = (float) Bank::where('condominium_id', $condominium->id)->sum('balance');
        $received = (float) $settlements->whereNotNull('receivable_id')->sum('amount');
        $paid = (float) $settlements->whereNotNull('payable_id')->sum('amount');

        return [
            'title' => 'Balancete Financeiro Gerencial',
            'kind' => 'gerencial_caixa',
            'as_of_date' => $asOf->toDateString(),
            'totals' => [
                'reported_bank_balance' => $reportedBalance,
                'received' => $received,
                'paid' => $paid,
                'receivables_open' => (float) $receivables->sum('open_amount'),
                'payables_open' => (float) $payables->sum('open_amount'),
                'projected_balance' => $reportedBalance + $receivables->sum('open_amount') - $payables->sum('open_amount'),
            ],
            'banks' => Bank::where('condominium_id', $condominium->id)->orderBy('name')->get(['name', 'agency', 'account_number', 'balance'])->map(fn ($bank) => [
                'name' => $bank->name,
                'account' => trim($bank->agency.' / '.$bank->account_number, ' /'),
                'balance' => (float) $bank->balance,
            ])->all(),
            'disclaimer' => 'Balancete financeiro gerencial. Não representa balancete contábil de débitos e créditos.',
        ];
    }

    private function openTitles(Collection $titles): Collection
    {
        return $titles->map(function ($title) {
            $title->open_amount = max(0, (float) $title->amount - (float) $title->settlements->sum('amount'));

            return $title;
        })->filter(fn ($title) => $title->open_amount > 0.004)->values();
    }

    private function forecastSeries(float $openingBalance, Collection $receivables, Collection $payables, Carbon $asOf, int $horizon): array
    {
        $balance = $openingBalance;

        return collect(range(0, $horizon))->map(function (int $offset) use (&$balance, $receivables, $payables, $asOf) {
            $date = $asOf->copy()->addDays($offset)->toDateString();
            $balance += (float) $receivables->filter(fn ($title) => $title->due_date->toDateString() === $date)->sum('open_amount');
            $balance -= (float) $payables->filter(fn ($title) => $title->due_date->toDateString() === $date)->sum('open_amount');

            return ['date' => $date, 'balance' => round($balance, 2)];
        })->all();
    }

    private function aging(Collection $titles, Carbon $asOf): array
    {
        return [
            'overdue' => (float) $titles->filter(fn ($title) => $title->due_date->lt($asOf))->sum('open_amount'),
            'next_30_days' => (float) $titles->filter(fn ($title) => $title->due_date->betweenIncluded($asOf, $asOf->copy()->addDays(30)))->sum('open_amount'),
            'next_90_days' => (float) $titles->filter(fn ($title) => $title->due_date->betweenIncluded($asOf->copy()->addDays(31), $asOf->copy()->addDays(90)))->sum('open_amount'),
        ];
    }

    private function budgetStatus(Condominium $condominium, Carbon $asOf): array
    {
        $budgeted = (float) BudgetItem::where('condominium_id', $condominium->id)->where('year', $asOf->year)->where('month', $asOf->month)->sum('amount');
        $actual = (float) FinanceSettlement::where('condominium_id', $condominium->id)->whereNotNull('payable_id')->whereMonth('settled_on', $asOf->month)->whereYear('settled_on', $asOf->year)->sum('amount');

        return [
            'budgeted' => $budgeted,
            'actual' => $actual,
            'variance' => $budgeted - $actual,
            'percentage_used' => $budgeted > 0 ? round(($actual / $budgeted) * 100, 2) : 0,
            'deviations' => [],
        ];
    }
}
