<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BudgetItem;
use App\Models\Payable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BudgetReportController extends Controller
{
    public function getReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:2100',
            'condominium_id' => 'required|exists:condominiums,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = $request->user();
        $condominiumId = (int) $request->input('condominium_id');

        if ($user === null || ! $user->belongsToCondominium($condominiumId)) {
            return response()->json([
                'message' => 'Acesso negado ao condomínio informado.',
            ], 403);
        }

        $year = $request->input('year');

        // Get all budgeted items for the year
        $budgeted = BudgetItem::where('year', $year)
            ->where('condominium_id', $condominiumId)
            ->get()
            ->groupBy('chart_account_id');

        // Get all actual expenses for the year
        $actuals = Payable::whereYear('due_date', $year)
            ->where('condominium_id', $condominiumId)
            ->whereNotNull('paid_at') // Consider only paid expenses
            ->select('chart_account_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('chart_account_id')
            ->get()
            ->keyBy('chart_account_id');

        $report = [];

        foreach ($budgeted as $accountId => $budgetItems) {
            $budgetedAmount = $budgetItems->sum('amount');
            $actualAmount = $actuals->has($accountId) ? $actuals->get($accountId)->total_amount : 0;
            $variance = $budgetedAmount - $actualAmount;

            $report[] = [
                'chart_account_id' => $accountId,
                'account_name' => $budgetItems->first()->chartAccount->name, // Assuming relationship exists
                'budgeted_amount' => $budgetedAmount,
                'actual_amount' => (float) $actualAmount,
                'variance' => $variance,
            ];
        }

        return response()->json($report);
    }
}
