<?php

namespace App\Http\Controllers\API;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\Transaction;
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

        $transactions = Transaction::where('condominium_id', $validated['condominium_id'])
            ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
            ->get();

        $receitas = $transactions->where('type', TransactionType::RECEITA)->sum('amount');
        $despesas = $transactions->where('type', TransactionType::DESPESA)->sum('amount');
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
            ->with(['transactions' => function ($q) use ($validated) {
                $q->whereBetween('date', [$validated['start_date'], $validated['end_date']]);
            }])->get();

        $pdf = Pdf::loadView('reports.balancete', [
            'accounts' => $accounts,
            'periodo' => [$validated['start_date'], $validated['end_date']],
        ]);

        return $pdf->download('balancete.pdf');
    }
}
