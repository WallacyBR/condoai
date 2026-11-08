<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Tower;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TowerController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        return Tower::query()
            ->with(['blocks', 'apartments'])
            ->orderByNameSmart()
            ->get();
    }

    public function store(Request $request)
    {
        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('towers')->where(fn ($query) => $query->where('condominium_id', $condoId)),
            ],
            'active' => 'boolean',
        ]);

        unset($validated['condominium_id']);
        $validated['condominium_id'] = $condoId;

        return Tower::create($validated);
    }

    public function show(Tower $tower)
    {
        return $tower->load(['blocks.apartments', 'apartments', 'condominium']);
    }

    public function update(Request $request, Tower $tower)
    {
        $condoId = $tower->condominium_id;

        $validated = $request->validate([
            'name' => [
                'string',
                'max:255',
                Rule::unique('towers')->where(function ($query) use ($condoId, $tower) {
                    return $query->where('condominium_id', $condoId)
                        ->where('id', '!=', $tower->id);
                }),
            ],
            'active' => 'boolean',
        ]);

        unset($validated['condominium_id']);
        $tower->update($validated);

        return $tower;
    }

    public function destroy(Tower $tower)
    {
        $tower->delete();

        return response()->json(null, 204);
    }
}
