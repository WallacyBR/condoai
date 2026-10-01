<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Concerns\ResolvesCurrentCondominium;
use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceInsightsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinanceDashboardController extends Controller
{
    use ResolvesCurrentCondominium;

    /**
     * Exibe os indicadores e projeções do condomínio atual.
     *
     * @queryParam as_of_date date Data-base. Example: 2026-06-26
     * @queryParam horizon_days integer Horizonte da projeção entre 7 e 365 dias. Example: 90
     */
    public function forecast(Request $request, FinanceInsightsService $insights)
    {
        $validated = $request->validate([
            'as_of_date' => ['nullable', 'date'],
            'horizon_days' => ['nullable', 'integer', 'min:7', 'max:365'],
        ]);

        return response()->json($insights->dashboard(
            $this->currentCondominium($request),
            Carbon::parse($validated['as_of_date'] ?? now()->toDateString())->startOfDay(),
            $validated['horizon_days'] ?? 90
        ));
    }
}
