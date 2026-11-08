<?php

namespace App\Http\Controllers\API;

use App\Enums\TransactionType;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashFlowController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        return CashFlow::query()->orderBy('date', 'desc')->get();
    }

    public function store(Request $request)
    {
        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'payable_id' => ['nullable', Rule::exists('payables', 'id')],
            'receivable_id' => ['nullable', Rule::exists('receivables', 'id')],
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'type' => ['required', Rule::in(TransactionType::values())],
            'description' => 'nullable|string',
        ]);

        unset($validated['condominium_id']);
        $validated['condominium_id'] = $condoId;

        return CashFlow::create($validated);
    }

    public function show(CashFlow $cashFlow)
    {
        return $cashFlow;
    }

    public function update(Request $request, CashFlow $cashFlow)
    {
        $validated = $request->validate([
            'payable_id' => ['nullable', Rule::exists('payables', 'id')],
            'receivable_id' => ['nullable', Rule::exists('receivables', 'id')],
            'date' => 'sometimes|date',
            'amount' => 'sometimes|numeric|min:0.01',
            'type' => ['sometimes', Rule::in(TransactionType::values())],
            'description' => 'nullable|string',
        ]);

        if (isset($validated['payable_id']) && isset($validated['receivable_id'])) {
            return response()->json(['message' => 'Cannot associate with both payable and receivable.'], 422);
        }

        unset($validated['condominium_id']);
        $cashFlow->update($validated);

        return $cashFlow;
    }

    public function destroy(CashFlow $cashFlow)
    {
        $cashFlow->delete();

        return response()->json(null, 204);
    }
}
