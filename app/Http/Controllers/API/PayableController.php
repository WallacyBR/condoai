<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Payable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PayableController extends Controller
{
    use ResolvesTenantCondominium;

    public function index()
    {
        $this->authorize('viewAny', Payable::class);

        return response()->json(Payable::all());
    }

    public function store(Request $request)
    {
        $this->authorize('create', Payable::class);

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
            'paid_at' => 'nullable|date',
        ]);

        unset($validated['condominium_id']);
        $validated['condominium_id'] = $condoId;

        $payable = Payable::create($validated);

        return response()->json($payable, 201);
    }

    public function show(Payable $payable)
    {
        $this->authorize('view', $payable);

        return response()->json($payable);
    }

    public function update(Request $request, Payable $payable)
    {
        $this->authorize('update', $payable);

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
            'paid_at' => 'nullable|date',
        ]);

        unset($validated['condominium_id']);
        $payable->update($validated);

        return response()->json($payable);
    }

    public function destroy(Payable $payable)
    {
        $this->authorize('delete', $payable);

        $payable->delete();

        return response()->json(null, 204);
    }
}
