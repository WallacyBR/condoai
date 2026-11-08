<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;

class FinanceDashboardController extends Controller
{
    public function forecast()
    {
        $amounts = Transaction::orderBy('date')
            ->pluck('amount');

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
