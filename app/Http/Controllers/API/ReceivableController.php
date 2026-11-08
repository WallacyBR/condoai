<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Concerns\ResolvesCurrentCondominium;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReceivableController extends Controller
{
    use ResolvesCurrentCondominium;
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Receivable::class);
        $condoId = $this->tenantCondominiumId($request);

        return response()->json(
            Receivable::with(['account:id,name', 'bank:id,name', 'settlements'])
                ->where('condominium_id', $condoId)
                ->latest('due_date')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $this->authorize('create', Receivable::class);
        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
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
            'received_at' => ['nullable', 'date'],
        ]);

        unset($validated['condominium_id']);
        $validated['condominium_id'] = $condoId;
        $validated['status'] = $validated['status'] ?? 'pending';
        $validated['competence_date'] = $validated['competence_date'] ?? $validated['due_date'];

        $receivable = Receivable::create($validated);

        return response()->json($receivable, 201);
    }

    public function show(Request $request, Receivable $receivable)
    {
        $this->tenant($request, $receivable);
        $this->authorize('view', $receivable);

        return response()->json($receivable->load(['account', 'bank', 'settlements.bank']));
    }

    public function update(Request $request, Receivable $receivable)
    {
        $this->tenant($request, $receivable);
        $this->authorize('update', $receivable);

        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'chart_account_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('chart_accounts', 'id')->where('condominium_id', $condoId),
            ],
            'bank_id' => [
                'nullable',
                'integer',
                Rule::exists('banks', 'id')->where('condominium_id', $condoId),
            ],
            'due_date' => ['sometimes', 'required', 'date'],
            'competence_date' => ['nullable', 'date'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'status' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:2000'],
            'counterpart_name' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'received_at' => ['nullable', 'date'],
        ]);

        unset($validated['condominium_id']);
        $receivable->update($validated);

        return response()->json($receivable->fresh());
    }

    public function destroy(Request $request, Receivable $receivable)
    {
        $this->tenant($request, $receivable);
        $this->authorize('delete', $receivable);

        abort_if($receivable->settlements()->exists(), 422, 'Títulos liquidados não podem ser excluídos.');

        $receivable->delete();

        return response()->noContent();
    }

    private function tenant(Request $request, Receivable $receivable): void
    {
        abort_unless($receivable->condominium_id === $this->tenantCondominiumId($request), 404);
    }
}
