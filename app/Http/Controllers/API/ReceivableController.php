<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Receivable;
use Illuminate\Http\Request;

class ReceivableController extends Controller
{
    use ResolvesTenantCondominium;

    public function index()
    {
        return response()->json(Receivable::all());
    }

    public function store(Request $request)
    {
        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'bank_id' => 'nullable|exists:banks,id',
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
        return response()->json($receivable);
    }

    public function update(Request $request, Receivable $receivable)
    {
        $validated = $request->validate([
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'bank_id' => 'nullable|exists:banks,id',
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
        $receivable->delete();

        return response()->json(null, 204);
    }
}
