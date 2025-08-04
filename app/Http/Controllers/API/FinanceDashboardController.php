<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payable;
use App\Models\Receivable;
use Illuminate\Support\Facades\DB;

class FinanceDashboardController extends Controller
{
    public function forecast()
    {
        $payables = Payable::orderBy('due_date')->get();
        $receivables = Receivable::orderBy('due_date')->get();

        $allTransactions = collect();

        foreach ($payables as $payable) {
            $allTransactions->push([
                'date' => $payable->due_date,
                'amount' => -$payable->amount, // Expenses are negative
            ]);
        }

        foreach ($receivables as $receivable) {
            $allTransactions->push([
                'date' => $receivable->due_date,
                'amount' => $receivable->amount,
            ]);
        }

        $allTransactions = $allTransactions->sortBy('date')->values();

        $amounts = $allTransactions->pluck('amount');

        $movingAverage = $amounts->avg() ?? 0;
        $trend = $this->calculateTrend($amounts);

        return [
            'moving_average' => (float) $movingAverage,
            'trend' => $trend,
        ];
    }

    private function calculateTrend($collection): float
    {
        $values = $collection->values();
        $n = $values->count();
        if ($n < 2) {
            return 0;
        }
        $x = range(1, $n);
        $sumX = array_sum($x);
        $sumY = $values->sum();
        $sumXY = 0;
        $sumX2 = 0;
        foreach ($values as $index => $y) {
            $xi = $index + 1;
            $sumXY += $xi * $y;
            $sumX2 += $xi * $xi;
        }
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX ** 2);

        return round($slope, 2);
    }
}
