<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Models\Tower;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TowerController extends Controller
{
    public function index(Request $request)
    {
        $query = Tower::with('condominium');

        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }

        $towers = $query->orderBy('name')->paginate(10);
        $condominiums = Condominium::pluck('name', 'id');

        return view('towers.index', compact('towers', 'condominiums'));
    }

    public function create()
    {
        $condominiums = Condominium::pluck('name', 'id');

        return view('towers.create', compact('condominiums'));
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
                }),
            ],
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id')->where(function ($query) {
                    return $query->where('active', true);
                }),
            ],
            'active' => 'boolean',
        ]);

        $tower = Tower::create($validated);

        return redirect()
            ->route('towers.show', $tower)
            ->with('success', 'Torre criada com sucesso!');
    }

    public function show(Tower $tower)
    {
        $tower->load(['blocks.apartments', 'condominium']);

        return view('towers.show', compact('tower'));
    }

    public function edit(Tower $tower)
    {
        $condominiums = Condominium::pluck('name', 'id');

        return view('towers.edit', compact('tower', 'condominiums'));
    }

    public function update(Request $request, Tower $tower)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('towers')->where(function ($query) use ($request, $tower) {
                    return $query->where('condominium_id', $request->condominium_id ?? $tower->condominium_id)
                        ->where('id', '!=', $tower->id);
                }),
            ],
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id')->where(function ($query) {
                    return $query->where('active', true);
                }),
            ],
            'active' => 'boolean',
        ]);

        $tower->update($validated);

        return redirect()
            ->route('towers.show', $tower)
            ->with('success', 'Torre atualizada com sucesso!');
    }

    public function destroy(Tower $tower)
    {
        $tower->delete();

        return redirect()
            ->route('towers.index')
            ->with('success', 'Torre removida com sucesso!');
    }
}
