<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Tower;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlockController extends Controller
{
    public function index(Request $request)
    {
        $query = Block::with(['tower.condominium']);

        if ($request->has('tower_id')) {
            $query->where('tower_id', $request->tower_id);
        }

        $blocks = $query->orderByNameSmart()->paginate(10);
        $towers = Tower::whereHas('condominium', function ($query) {
            $query->where('active', true);
        })->pluck('name', 'id');

        return view('blocks.index', compact('blocks', 'towers'));
    }

    public function create(Request $request)
    {
        $towers = Tower::whereHas('condominium', function ($query) {
            $query->where('active', true);
        })->pluck('name', 'id');

        $selectedTower = $request->tower_id;

        return view('blocks.create', compact('towers', 'selectedTower'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blocks')->where(function ($query) use ($request) {
                    return $query->where('tower_id', $request->tower_id);
                }),
            ],
            'tower_id' => [
                'required',
                Rule::exists('towers', 'id')->where(function ($query) {
                    return $query->whereHas('condominium', function ($q) {
                        $q->where('active', true);
                    });
                }),
            ],
            'active' => 'boolean',
        ]);

        $block = Block::create($validated);

        return redirect()
            ->route('blocks.show', $block)
            ->with('success', 'Bloco criado com sucesso!');
    }

    public function show(Block $block)
    {
        $block->load(['tower.condominium', 'apartments']);

        return view('blocks.show', compact('block'));
    }

    public function edit(Block $block)
    {
        $towers = Tower::whereHas('condominium', function ($query) {
            $query->where('active', true);
        })->pluck('name', 'id');

        return view('blocks.edit', compact('block', 'towers'));
    }

    public function update(Request $request, Block $block)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blocks')->where(function ($query) use ($request, $block) {
                    return $query->where('tower_id', $request->tower_id ?? $block->tower_id)
                        ->where('id', '!=', $block->id);
                }),
            ],
            'tower_id' => [
                'required',
                Rule::exists('towers', 'id')->where(function ($query) {
                    return $query->whereHas('condominium', function ($q) {
                        $q->where('active', true);
                    });
                }),
            ],
            'active' => 'boolean',
        ]);

        $block->update($validated);

        return redirect()
            ->route('blocks.show', $block)
            ->with('success', 'Bloco atualizado com sucesso!');
    }

    public function destroy(Block $block)
    {
        $block->delete();

        return redirect()
            ->route('blocks.index')
            ->with('success', 'Bloco removido com sucesso!');
    }
}
