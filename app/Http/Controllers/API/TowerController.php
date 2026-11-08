<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tower;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TowerController extends Controller
{
    public function index(Request $request)
    {
        $query = Tower::query();
        
        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }

        return $query->with(['blocks'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('towers')->where(function ($query) use ($request) {
                    return $query->where('condominium_id', $request->condominium_id);
                })
            ],
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id')->where(function ($query) {
                    return $query->where('active', true);
                }),
            ],
            'active' => 'boolean'
        ]);

        return Tower::create($validated);
    }

    public function show(Tower $tower)
    {
        return $tower->load(['blocks.apartments', 'condominium']);
    }

    public function update(Request $request, Tower $tower)
    {
        $validated = $request->validate([
            'name' => [
                'string',
                'max:255',
                Rule::unique('towers')->where(function ($query) use ($request, $tower) {
                    return $query->where('condominium_id', $request->condominium_id ?? $tower->condominium_id)
                                ->where('id', '!=', $tower->id);
                })
            ],
            'condominium_id' => [
                Rule::exists('condominiums', 'id')->where(function ($query) {
                    return $query->where('active', true);
                }),
            ],
            'active' => 'boolean'
        ]);

        $tower->update($validated);
        return $tower;
    }

    public function destroy(Tower $tower)
    {
        $tower->delete();
        return response()->json(null, 204);
    }
}
