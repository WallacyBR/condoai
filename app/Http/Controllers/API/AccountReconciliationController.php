<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccountHistory;
use App\Models\Bank;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountReconciliationController extends Controller
{
    public function reconcile(Request $request)
    {
        $validated = $request->validate([
            'bank_id' => ['required', Rule::exists('banks', 'id')],
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => ['integer', Rule::exists('transactions', 'id')],
            'notes' => 'nullable|string',
        ]);

        $bank = Bank::findOrFail($validated['bank_id']);
        $now = now();
        foreach ($validated['transaction_ids'] as $id) {
            $transaction = Transaction::find($id);
            if ($transaction) {
                $transaction->update(['status' => 'conciliado']);
                AccountHistory::create([
                    'bank_id' => $bank->id,
                    'transaction_id' => $transaction->id,
                    'reconciled_at' => $now,
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
        }

        return response()->json(['message' => 'Conciliação registrada']);
    }

    public function history(Request $request)
    {
        $validated = $request->validate([
            'bank_id' => ['required', Rule::exists('banks', 'id')],
        ]);

        return AccountHistory::where('bank_id', $validated['bank_id'])
            ->orderByDesc('reconciled_at')
            ->get();
    }
}
