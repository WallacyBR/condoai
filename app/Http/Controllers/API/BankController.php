<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        return Bank::query()->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'agency' => 'nullable|string|max:50',
            'account_number' => 'required|string|max:50',
            'balance' => 'numeric|min:0',
        ]);

        unset($validated['condominium_id']);
        $validated['condominium_id'] = $condoId;

        return Bank::create($validated);
    }

    public function show(Bank $bank)
    {
        return $bank;
    }

    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'agency' => 'nullable|string|max:50',
            'account_number' => 'string|max:50',
            'balance' => 'numeric|min:0',
        ]);

        unset($validated['condominium_id']);
        $bank->update($validated);

        return $bank;
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();

        return response()->json(null, 204);
    }
}
