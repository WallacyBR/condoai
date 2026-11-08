<?php

namespace App\Http\Controllers\API;

use App\Enums\TransactionType;
use App\Http\Controllers\API\Concerns\ResolvesCurrentCondominium;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\CashFlow;
use App\Models\Payable;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    use ResolvesCurrentCondominium;
    use ResolvesTenantCondominium;

    /**
     * Lista títulos financeiros de receita e despesa do condomínio atual.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:payable,receivable'],
            'query' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $condominiumId = $this->tenantCondominiumId($request);
        $titles = collect();

        if (($validated['type'] ?? null) !== 'receivable') {
            $titles = $titles->merge($this->titles(Payable::class, 'payable', $condominiumId, $validated));
        }

        if (($validated['type'] ?? null) !== 'payable') {
            $titles = $titles->merge($this->titles(Receivable::class, 'receivable', $condominiumId, $validated));
        }

        return response()->json($titles->sortByDesc('due_date')->values());
    }

    /**
     * Cria um título financeiro compatível com a tela de lançamentos.
     */
    public function store(Request $request)
    {
        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'type' => ['required', 'in:payable,receivable'],
            'chart_account_id' => [
                'required',
                'integer',
                Rule::exists('chart_accounts', 'id')->where('condominium_id', $condoId),
            ],
            'bank_id' => [
                'nullable',
                'integer',
                Rule::exists('banks', 'id')->where('condominium_id', $condoId),
            ],
            'due_date' => ['required', 'date'],
            'competence_date' => ['nullable', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'status' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:2000'],
            'counterpart_name' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'received_at' => ['nullable', 'date'],
        ]);

        try {
            DB::beginTransaction();

            $type = $validated['type'];
            unset($validated['type'], $validated['condominium_id']);

            $data = [
                ...$validated,
                'condominium_id' => $condoId,
                'competence_date' => $validated['competence_date'] ?? $validated['due_date'],
                'status' => $validated['status'] ?? 'pending',
            ];

            if ($type === 'payable') {
                unset($data['received_at']);
                $transaction = Payable::create($data);
                CashFlow::create([
                    'condominium_id' => $transaction->condominium_id,
                    'payable_id' => $transaction->id,
                    'date' => $transaction->due_date,
                    'amount' => $transaction->amount,
                    'type' => TransactionType::DESPESA,
                    'description' => $transaction->description,
                ]);
            } else {
                unset($data['paid_at']);
                $transaction = Receivable::create($data);
                CashFlow::create([
                    'condominium_id' => $transaction->condominium_id,
                    'receivable_id' => $transaction->id,
                    'date' => $transaction->due_date,
                    'amount' => $transaction->amount,
                    'type' => TransactionType::RECEITA,
                    'description' => $transaction->description,
                ]);
            }

            DB::commit();

            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Error creating transaction', 'error' => $e->getMessage()], 500);
        }
    }

    private function titles(string $model, string $type, int $condominiumId, array $filters): Collection
    {
        $query = $model::with(['account:id,name', 'bank:id,name'])->where('condominium_id', $condominiumId);

        if (! empty($filters['query'])) {
            $query->where('description', 'like', '%'.$filters['query'].'%');
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('due_date', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('due_date', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($title) use ($type) {
            $title->type = $type;

            return $title;
        });
    }
}
