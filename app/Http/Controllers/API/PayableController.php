<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Payable;
use Illuminate\Http\Request;

class PayableController extends Controller
{
    use ResolvesTenantCondominium;

    public function index()
    {
        return response()->json(Payable::all());
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
            'paid_at' => 'nullable|date',
        ]);

        unset($validated['condominium_id']);
        $validated['condominium_id'] = $condoId;

        $payable = Payable::create($validated);

        return response()->json($payable, 201);
    }

    public function show(Payable $payable)
    {
        return response()->json($payable);
    }

    public function update(Request $request, Payable $payable)
    {
        $validated = $request->validate([
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'bank_id' => 'nullable|exists:banks,id',
            'due_date' => 'required|date',
            'amount' => 'required|numeric',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        unset($validated['condominium_id']);
        $payable->update($validated);

        return response()->json($payable);
    }

    public function destroy(Payable $payable)
    {
        $payable->delete();

        return response()->json(null, 204);
    }
}
