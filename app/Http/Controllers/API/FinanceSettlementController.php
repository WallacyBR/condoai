<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Concerns\ResolvesCurrentCondominium;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\FinanceSettlement;
use App\Models\Payable;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceSettlementController extends Controller
{
    use ResolvesCurrentCondominium;

    public function storePayable(Request $request, Payable $payable)
    {
        return $this->store($request, $payable, 'payable_id');
    }

    public function storeReceivable(Request $request, Receivable $receivable)
    {
        return $this->store($request, $receivable, 'receivable_id');
    }

    private function store(Request $request, Payable|Receivable $title, string $foreignKey)
    {
        $condominium = $this->currentCondominium($request);
        abort_unless($title->condominium_id === $condominium->id, 404);

        $validated = $request->validate([
            'bank_id' => ['nullable', 'integer'],
            'settled_on' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! empty($validated['bank_id']) && ! Bank::where('condominium_id', $condominium->id)->whereKey($validated['bank_id'])->exists()) {
            throw ValidationException::withMessages(['bank_id' => 'A conta bancária não pertence ao condomínio atual.']);
        }

        return DB::transaction(function () use ($request, $title, $foreignKey, $validated, $condominium) {
            $settled = (float) $title->settlements()->sum('amount');
            if ((float) $validated['amount'] > ((float) $title->amount - $settled + 0.004)) {
                throw ValidationException::withMessages(['amount' => 'A liquidação é maior que o saldo em aberto do título.']);
            }

            $settlement = FinanceSettlement::create([
                'condominium_id' => $condominium->id,
                $foreignKey => $title->id,
                'bank_id' => $validated['bank_id'] ?? null,
                'created_by' => $request->user()->id,
                ...$validated,
            ]);
            $total = $settled + (float) $settlement->amount;
            $status = $total + 0.004 >= (float) $title->amount ? 'paid' : 'partially_paid';
            $dateColumn = $foreignKey === 'payable_id' ? 'paid_at' : 'received_at';
            $title->update(['status' => $status, $dateColumn => $status === 'paid' ? $settlement->settled_on : null]);

            return response()->json($settlement->load('bank'), 201);
        });
    }
}
