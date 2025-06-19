<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlockController extends Controller
{
    public function index(Request $request)
    {
        $query = Block::query();

        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }

        if ($request->has('tower_id')) {
            $query->where('tower_id', $request->tower_id);
        }

        return $query->with(['tower', 'apartments'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blocks')->where(function ($query) use ($request) {
                    return $query->where('condominium_id', $request->condominium_id)
                        ->where('tower_id', $request->tower_id);
                }),
            ],
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id')->where(function ($query) {
                    return $query->where('active', true);
                }),
            ],
            'tower_id' => [
                'nullable',
                Rule::exists('towers', 'id')->where(function ($query) {
                    return $query->where('active', true);
                }),
            ],
            'active' => 'boolean',
        ]);

        $block = Block::create($validated);
        return $block->load('tower');
    }

    public function show(Block $block)
    {
        return $block->load(['tower', 'apartments.residents', 'condominium']);
    }

    public function update(Request $request, Block $block)
    {
        $validated = $request->validate([
            'name' => [
                'string',
                'max:255',
                Rule::unique('blocks')->ignore($block->id)->where(function ($query) use ($request, $block) {
                    return $query->where('condominium_id', $request->input('condominium_id', $block->condominium_id))
                        ->where('tower_id', $request->input('tower_id', $block->tower_id));
                }),
            ],
            'condominium_id' => [
                Rule::exists('condominiums', 'id')->where(function ($query) {
                    return $query->where('active', true);
                }),
            ],
            'tower_id' => [
                'nullable',
                Rule::exists('towers', 'id')->where(function ($query) {
                    return $query->where('active', true);
                }),
            ],
            'active' => 'boolean',
        ]);

        $block->update($validated);

        return $block;
    }

    public function destroy(Block $block)
    {
        $block->delete();

        return response()->json(null, 204);
    }
}
