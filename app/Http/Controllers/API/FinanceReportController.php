<?php

namespace App\Http\Controllers\API;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\Payable;
use App\Models\Receivable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinanceReportController extends Controller
{
    public function dre(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => ['required', Rule::exists('condominiums', 'id')],
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $payables = Payable::where('condominium_id', $validated['condominium_id'])
            ->whereBetween('due_date', [$validated['start_date'], $validated['end_date']])
            ->get();

        $receivables = Receivable::where('condominium_id', $validated['condominium_id'])
            ->whereBetween('due_date', [$validated['start_date'], $validated['end_date']])
            ->get();

        $receitas = $receivables->sum('amount');
        $despesas = $payables->sum('amount');
        $resultado = $receitas - $despesas;

        $pdf = Pdf::loadView('reports.dre', [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'resultado' => $resultado,
            'periodo' => [$validated['start_date'], $validated['end_date']],
        ]);

        return $pdf->download('dre.pdf');
    }

    public function balancete(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => ['required', Rule::exists('condominiums', 'id')],
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $accounts = ChartAccount::where('condominium_id', $validated['condominium_id'])
            ->with(['payables' => function ($q) use ($validated) {
                $q->whereBetween('due_date', [$validated['start_date'], $validated['end_date']]);
            }, 'receivables' => function ($q) use ($validated) {
                $q->whereBetween('due_date', [$validated['start_date'], $validated['end_date']]);
            }])->get();

        $reportData = [];
        foreach ($accounts as $account) {
            $debit = 0;
            $credit = 0;

            foreach ($account->payables as $payable) {
                $debit += $payable->amount;
            }

            foreach ($account->receivables as $receivable) {
                $credit += $receivable->amount;
            }

            $reportData[] = [
                'account' => $account->name,
                'type' => $account->type->value,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $credit - $debit,
            ];
        }

        $pdf = Pdf::loadView('reports.balancete', [
            'accounts' => $accounts,
            'periodo' => [$validated['start_date'], $validated['end_date']],
        ]);

        return $pdf->download('balancete.pdf');
    }
}
