<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Concerns\ResolvesCurrentCondominium;
use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceInsightsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinanceReportController extends Controller
{
    use ResolvesCurrentCondominium;

    /**
     * Gera DRE gerencial por competência em tela ou PDF.
     *
     * @queryParam start_date date Data inicial obrigatória. Example: 2026-06-01
     * @queryParam end_date date Data final obrigatória. Example: 2026-06-30
     * @queryParam format string json ou pdf. Example: json
     */
    public function dre(Request $request, FinanceInsightsService $insights)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'format' => ['nullable', 'in:json,pdf'],
        ]);
        $condominium = $this->currentCondominium($request);
        $report = $insights->dre($condominium, Carbon::parse($validated['start_date'])->startOfDay(), Carbon::parse($validated['end_date'])->endOfDay());

        return $this->respond($report, $condominium->name, 'dre-gerencial', $validated['format'] ?? 'json');
    }

    /**
     * Gera balancete financeiro gerencial em tela ou PDF.
     *
     * @queryParam as_of_date date Data-base obrigatória. Example: 2026-06-30
     * @queryParam format string json ou pdf. Example: json
     */
    public function balancete(Request $request, FinanceInsightsService $insights)
    {
        $validated = $request->validate([
            'as_of_date' => ['required', 'date'],
            'format' => ['nullable', 'in:json,pdf'],
        ]);
        $condominium = $this->currentCondominium($request);
        $report = $insights->balancete($condominium, Carbon::parse($validated['as_of_date'])->endOfDay());

        return $this->respond($report, $condominium->name, 'balancete-financeiro-gerencial', $validated['format'] ?? 'json');
    }

    private function respond(array $report, string $condominiumName, string $filename, string $format)
    {
        if ($format === 'json') {
            return response()->json($report);
        }

        return Pdf::loadView('reports.finance-gerencial', compact('report', 'condominiumName'))
            ->download($filename.'.pdf');
    }
}
