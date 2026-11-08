<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Block;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Apartment::with(['block.tower'])->withCount('residents');

        if ($request->has('block_id')) {
            $query->where('block_id', $request->block_id);
        }

        $apartments = $query->orderBy('number')->paginate(10);
        $blocks = Block::pluck('name', 'id');

        return view('apartments.index', compact('apartments', 'blocks'));
    }

    public function create(Request $request)
    {
        $blocks = Block::pluck('name', 'id');
        $residents = Resident::pluck('name', 'id');
        $selectedBlock = $request->block_id;

        return view('apartments.create', compact('blocks', 'residents', 'selectedBlock'));
    }

    public function store(Request $request)
    {
        $block = Block::findOrFail($request->block_id);

        $validated = $request->validate([
            'number' => [
                'required',
                'string',
                'max:10',
                Rule::unique('apartments')->where(function ($query) use ($block) {
                    return $query->whereHas('block', function ($q) use ($block) {
                        $q->where('condominium_id', $block->condominium_id);
                    });
                })
            ],
            'floor' => 'required|integer',
            'block_id' => [
                'required',
                Rule::exists('blocks', 'id')->where(fn($q) => $q->where('active', true)),
            ],
            'owner_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(function ($q) use ($block) {
                    return $q->where('active', true)->where('condominium_id', $block->condominium_id);
                }),
            ],
            'tenant_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(function ($q) use ($block) {
                    return $q->where('active', true)->where('condominium_id', $block->condominium_id);
                }),
            ],
            'active' => 'boolean'
        ]);

        $apartment = Apartment::create($validated);

        if (!empty($validated['owner_id'])) {
            $apartment->residents()->attach($validated['owner_id'], ['type' => 'owner']);
        }

        if (!empty($validated['tenant_id'])) {
            $apartment->residents()->attach($validated['tenant_id'], ['type' => 'tenant']);
        }

        return redirect()
            ->route('apartments.show', $apartment)
            ->with('success', 'Apartamento criado com sucesso!');
    }

    public function show(Apartment $apartment)
    {
        $apartment->load(['block.tower', 'owner', 'tenant', 'residents']);
        return view('apartments.show', compact('apartment'));
    }

    public function edit(Apartment $apartment)
    {
        $blocks = Block::pluck('name', 'id');
        $residents = Resident::pluck('name', 'id');
        return view('apartments.edit', compact('apartment', 'blocks', 'residents'));
    }

    public function update(Request $request, Apartment $apartment)
    {
        $block = $apartment->block;

        $validated = $request->validate([
            'number' => [
                'string',
                'max:10',
                Rule::unique('apartments')->where(function ($query) use ($block, $apartment) {
                    return $query->whereHas('block', function ($q) use ($block) {
                        $q->where('condominium_id', $block->condominium_id);
                    })->where('id', '!=', $apartment->id);
                })
            ],
            'floor' => 'integer',
            'block_id' => [
                Rule::exists('blocks', 'id')->where(fn($q) => $q->where('active', true)),
            ],
            'owner_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(function ($q) use ($block) {
                    return $q->where('active', true)->where('condominium_id', $block->condominium_id);
                }),
            ],
            'tenant_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(function ($q) use ($block) {
                    return $q->where('active', true)->where('condominium_id', $block->condominium_id);
                }),
            ],
            'active' => 'boolean'
        ]);

        if (isset($validated['owner_id']) && $apartment->owner_id !== $validated['owner_id']) {
            if ($apartment->owner_id) {
                $apartment->residents()->wherePivot('type', 'owner')->detach();
            }
            if ($validated['owner_id']) {
                $apartment->residents()->attach($validated['owner_id'], ['type' => 'owner']);
            }
        }

        if (isset($validated['tenant_id']) && $apartment->tenant_id !== $validated['tenant_id']) {
            if ($apartment->tenant_id) {
                $apartment->residents()->wherePivot('type', 'tenant')->detach();
            }
            if ($validated['tenant_id']) {
                $apartment->residents()->attach($validated['tenant_id'], ['type' => 'tenant']);
            }
        }

        $apartment->update($validated);

        return redirect()
            ->route('apartments.show', $apartment)
            ->with('success', 'Apartamento atualizado com sucesso!');
    }

    public function destroy(Apartment $apartment)
    {
        $apartment->delete();

        return redirect()
            ->route('apartments.index')
            ->with('success', 'Apartamento removido com sucesso!');
    }
}
