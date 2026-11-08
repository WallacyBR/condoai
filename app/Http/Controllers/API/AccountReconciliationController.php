<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccountHistory;
use App\Models\Bank;
use App\Models\Payable;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountReconciliationController extends Controller
{
    public function reconcile(Request $request)
    {
        $validated = $request->validate([
            'bank_id' => ['required', Rule::exists('banks', 'id')],
            'payable_ids' => 'nullable|array',
            'payable_ids.*' => ['integer', Rule::exists('payables', 'id')],
            'receivable_ids' => 'nullable|array',
            'receivable_ids.*' => ['integer', Rule::exists('receivables', 'id')],
            'notes' => 'nullable|string',
        ]);

        $bank = Bank::findOrFail($validated['bank_id']);
        $now = now();

        if (isset($validated['payable_ids'])) {
            foreach ($validated['payable_ids'] as $id) {
                $payable = Payable::find($id);
                if ($payable) {
                    $payable->update(['status' => 'conciliado']);
                    AccountHistory::create([
                        'bank_id' => $bank->id,
                        'payable_id' => $payable->id,
                        'reconciled_at' => $now,
                        'notes' => $validated['notes'] ?? null,
                    ]);
                }
            }
        }

        if (isset($validated['receivable_ids'])) {
            foreach ($validated['receivable_ids'] as $id) {
                $receivable = Receivable::find($id);
                if ($receivable) {
                    $receivable->update(['status' => 'conciliado']);
                    AccountHistory::create([
                        'bank_id' => $bank->id,
                        'receivable_id' => $receivable->id,
                        'reconciled_at' => $now,
                        'notes' => $validated['notes'] ?? null,
                    ]);
                }
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
            ->with(['payable', 'receivable'])
            ->orderByDesc('reconciled_at')
            ->get();
    }
}
