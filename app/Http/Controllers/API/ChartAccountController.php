<?php

namespace App\Http\Controllers\API;

use App\Enums\AccountType;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartAccountController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        $this->authorize('viewAny', ChartAccount::class);

        $query = ChartAccount::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return $query->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $this->authorize('create', ChartAccount::class);

        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                Rule::exists('chart_accounts', 'id')->where('condominium_id', $condoId),
            ],
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(AccountType::values())],
            'account_number' => 'nullable|string|max:50',
        ]);

        unset($validated['condominium_id']);
        $validated['condominium_id'] = $condoId;

        return ChartAccount::create($validated);
    }

    public function show(ChartAccount $chartAccount)
    {
        $this->authorize('view', $chartAccount);

        return $chartAccount;
    }

    public function update(Request $request, ChartAccount $chartAccount)
    {
        $this->authorize('update', $chartAccount);

        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                Rule::exists('chart_accounts', 'id')->where('condominium_id', $condoId),
            ],
            'name' => 'string|max:255',
            'type' => [Rule::in(AccountType::values())],
            'account_number' => 'nullable|string|max:50',
        ]);

        unset($validated['condominium_id']);
        $chartAccount->update($validated);

        return $chartAccount;
    }

    public function destroy(ChartAccount $chartAccount)
    {
        $this->authorize('delete', $chartAccount);

        $chartAccount->delete();

        return response()->json(null, 204);
    }
}
