<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReceivableController extends Controller
{
    use ResolvesTenantCondominium;

    public function index()
    {
        $this->authorize('viewAny', Receivable::class);

        return response()->json(Receivable::all());
    }

    public function store(Request $request)
    {
        $this->authorize('create', Receivable::class);

        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'chart_account_id' => [
                'required',
                Rule::exists('chart_accounts', 'id')->where('condominium_id', $condoId),
            ],
            'bank_id' => [
                'nullable',
                Rule::exists('banks', 'id')->where('condominium_id', $condoId),
            ],
            'due_date' => 'required|date',
            'amount' => 'required|numeric',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'received_at' => 'nullable|date',
        ]);

        unset($validated['condominium_id']);
        $validated['condominium_id'] = $condoId;

        $receivable = Receivable::create($validated);

        return response()->json($receivable, 201);
    }

    public function show(Receivable $receivable)
    {
        $this->authorize('view', $receivable);

        return response()->json($receivable);
    }

    public function update(Request $request, Receivable $receivable)
    {
        $this->authorize('update', $receivable);

        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'chart_account_id' => [
                'required',
                Rule::exists('chart_accounts', 'id')->where('condominium_id', $condoId),
            ],
            'bank_id' => [
                'nullable',
                Rule::exists('banks', 'id')->where('condominium_id', $condoId),
            ],
            'due_date' => 'required|date',
            'amount' => 'required|numeric',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'received_at' => 'nullable|date',
        ]);

        unset($validated['condominium_id']);
        $receivable->update($validated);

        return response()->json($receivable);
    }

    public function destroy(Receivable $receivable)
    {
        $this->authorize('delete', $receivable);

        $receivable->delete();

        return response()->json(null, 204);
    }
}
