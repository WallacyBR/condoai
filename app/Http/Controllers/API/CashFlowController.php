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
            'transaction_id' => ['required', Rule::exists('transactions', 'id')],
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
            'transaction_id' => ['sometimes', Rule::exists('transactions', 'id')],
            'date' => 'date',
            'amount' => 'numeric|min:0.01',
            'type' => [Rule::in(TransactionType::values())],
            'description' => 'nullable|string',
        ]);

        $cashFlow->update($validated);

        return $cashFlow;
    }

    public function destroy(CashFlow $cashFlow)
    {
        $cashFlow->delete();
        return response()->json(null, 204);
    }
}
