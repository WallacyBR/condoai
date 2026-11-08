<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Apartment::query();
        
        if ($request->has('block_id')) {
            $query->where('block_id', $request->block_id);
        }

        return $query->with(['owner', 'tenant', 'residents', 'block.tower'])->get();
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
                Rule::exists('blocks', 'id')->where(function ($query) {
                    return $query->where('active', true);
                }),
            ],
            'owner_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(function ($query) use ($block) {
                    return $query->where('active', true)
                                ->where('condominium_id', $block->condominium_id);
                }),
            ],
            'tenant_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(function ($query) use ($block) {
                    return $query->where('active', true)
                                ->where('condominium_id', $block->condominium_id);
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

        return $apartment->load(['owner', 'tenant', 'residents']);
    }

    public function show(Apartment $apartment)
    {
        return $apartment->load(['block.tower', 'owner', 'tenant', 'residents']);
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
                Rule::exists('blocks', 'id')->where(function ($query) {
                    return $query->where('active', true);
                }),
            ],
            'owner_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(function ($query) use ($block) {
                    return $query->where('active', true)
                                ->where('condominium_id', $block->condominium_id);
                }),
            ],
            'tenant_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(function ($query) use ($block) {
                    return $query->where('active', true)
                                ->where('condominium_id', $block->condominium_id);
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
        return $apartment->load(['owner', 'tenant', 'residents']);
    }

    public function destroy(Apartment $apartment)
    {
        $apartment->delete();
        return response()->json(null, 204);
    }

    public function addResident(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'resident_id' => [
                'required',
                Rule::exists('residents', 'id')->where(function ($query) use ($apartment) {
                    return $query->where('active', true)
                                ->where('condominium_id', $apartment->block->condominium_id);
                }),
            ]
        ]);

        $apartment->residents()->attach($validated['resident_id'], [
            'type' => 'resident',
            'active' => true
        ]);

        return $apartment->load(['residents']);
    }

    public function removeResident(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'resident_id' => [
                'required',
                Rule::exists('residents', 'id'),
            ]
        ]);

        $apartment->residents()->wherePivot('type', 'resident')->detach($validated['resident_id']);

        return $apartment->load(['residents']);
    }
}
