<?php

namespace App\Http\Controllers\API;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query();
        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }
        return $query->orderBy('date', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'chart_account_id' => ['required', Rule::exists('chart_accounts', 'id')],
            'condominium_id' => ['required', Rule::exists('condominiums', 'id')],
            'created_by' => ['required', Rule::exists('users', 'id')],
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'type' => ['required', Rule::in(TransactionType::values())],
            'status' => 'required|string',
            'description' => 'nullable|string',
        ]);

        return Transaction::create($validated);
    }

    public function show(Transaction $transaction)
    {
        return $transaction;
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'chart_account_id' => ['sometimes', Rule::exists('chart_accounts', 'id')],
            'condominium_id' => ['sometimes', Rule::exists('condominiums', 'id')],
            'created_by' => ['sometimes', Rule::exists('users', 'id')],
            'date' => 'date',
            'amount' => 'numeric|min:0.01',
            'type' => [Rule::in(TransactionType::values())],
            'status' => 'string',
            'description' => 'nullable|string',
        ]);

        $transaction->update($validated);

        return $transaction;
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(null, 204);
    }
}
