<?php

namespace App\Http\Controllers\API;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        $query = CashFlow::query();
        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }
        return $query->orderBy('date', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => ['required', Rule::exists('condominiums', 'id')],
            'payable_id' => ['nullable', Rule::exists('payables', 'id')],
            'receivable_id' => ['nullable', Rule::exists('receivables', 'id')],
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'type' => ['required', Rule::in(TransactionType::values())],
            'description' => 'nullable|string',
        ]);

        return CashFlow::create($validated);
    }

    public function show(CashFlow $cashFlow)
    {
        return $cashFlow;
    }

    public function update(Request $request, CashFlow $cashFlow)
    {
        $validated = $request->validate([
            'condominium_id' => ['sometimes', Rule::exists('condominiums', 'id')],
            'payable_id' => ['nullable', Rule::exists('payables', 'id')],
            'receivable_id' => ['nullable', Rule::exists('receivables', 'id')],
            'date' => 'sometimes|date',
            'amount' => 'sometimes|numeric|min:0.01',
            'type' => ['sometimes', Rule::in(TransactionType::values())],
            'description' => 'nullable|string',
        ]);

        // Ensure only one of payable_id or receivable_id is present if they are being updated
        if (isset($validated['payable_id']) && isset($validated['receivable_id'])) {
            return response()->json(['message' => 'Cannot associate with both payable and receivable.'], 422);
        }

        $cashFlow->update($validated);

        return $cashFlow;
    }

    public function destroy(CashFlow $cashFlow)
    {
        $cashFlow->delete();
        return response()->json(null, 204);
    }
}
