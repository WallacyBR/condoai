<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlockController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        $query = Block::query();

        if ($request->has('tower_id')) {
            $query->where('tower_id', $request->tower_id);
        }

        return $query->with(['tower', 'apartments'])->orderByNameSmart()->get();
    }

    public function store(Request $request)
    {
        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blocks')->where(function ($query) use ($request, $condoId) {
                    return $query->where('condominium_id', $condoId)
                        ->where('tower_id', $request->tower_id);
                }),
            ],
            'tower_id' => [
                'nullable',
                Rule::exists('towers', 'id')->where(fn ($query) => $query->where('condominium_id', $condoId)->where('active', true)),
            ],
            'active' => 'boolean',
        ]);

        unset($validated['condominium_id']);
        $validated['condominium_id'] = $condoId;

        $block = Block::create($validated);

        return $block->load('tower');
    }

    public function show(Block $block)
    {
        return $block->load(['tower', 'apartments.residents', 'condominium']);
    }

    public function update(Request $request, Block $block)
    {
        $condoId = $block->condominium_id;

        $validated = $request->validate([
            'name' => [
                'string',
                'max:255',
                Rule::unique('blocks')->ignore($block->id)->where(function ($query) use ($request, $block, $condoId) {
                    return $query->where('condominium_id', $condoId)
                        ->where('tower_id', $request->input('tower_id', $block->tower_id));
                }),
            ],
            'tower_id' => [
                'nullable',
                Rule::exists('towers', 'id')->where(fn ($query) => $query->where('condominium_id', $condoId)->where('active', true)),
            ],
            'active' => 'boolean',
        ]);

        unset($validated['condominium_id']);
        $block->update($validated);

        return $block;
    }

    public function destroy(Block $block)
    {
        $block->delete();

        return response()->json(null, 204);
    }
}
