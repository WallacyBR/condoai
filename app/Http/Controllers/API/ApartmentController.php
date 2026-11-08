<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApartmentController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        $query = Apartment::query();

        if ($request->has('block_id')) {
            $query->where('block_id', $request->block_id);
        }

        if ($request->has('tower_id')) {
            $query->where('tower_id', $request->tower_id);
        }

        $apartments = $query->with(['owner', 'tenant', 'residents', 'block', 'tower'])->orderByFieldSmart('number')->get();

        $apartments->each(function ($apartment) {
            $apartment->resident = $apartment->owner ?: $apartment->tenant;
        });

        return $apartments;
    }

    public function store(Request $request)
    {
        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'number' => [
                'required',
                'string',
                'max:10',
                Rule::unique('apartments')->where(function ($query) use ($request, $condoId) {
                    $query->where('condominium_id', $condoId);

                    if ($request->block_id) {
                        $query->where('block_id', $request->block_id);
                    }

                    if ($request->tower_id && ! $request->block_id) {
                        $query->where('tower_id', $request->tower_id);
                    }

                    return $query;
                }),
            ],
            'floor' => 'required|integer',
            'tower_id' => [
                'nullable',
                Rule::exists('towers', 'id')->where(fn ($q) => $q->where('condominium_id', $condoId)->where('active', true)),
            ],
            'block_id' => [
                'nullable',
                Rule::exists('blocks', 'id')->where(fn ($q) => $q->where('condominium_id', $condoId)->where('active', true)),
            ],
            'owner_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(fn ($q) => $q->where('active', true)->where('condominium_id', $condoId)),
            ],
            'tenant_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(fn ($q) => $q->where('active', true)->where('condominium_id', $condoId)),
            ],
            'active' => 'boolean',
        ]);

        try {
            unset($validated['condominium_id']);
            $validated['condominium_id'] = $condoId;

            $apartment = Apartment::create($validated);

            if (! empty($validated['owner_id'])) {
                $apartment->residents()->attach($validated['owner_id'], ['type' => 'owner']);
            }

            if (! empty($validated['tenant_id'])) {
                $apartment->residents()->attach($validated['tenant_id'], ['type' => 'tenant']);
            }

            $apartment->load(['owner', 'tenant', 'residents', 'block', 'tower']);
            $apartment->resident = $apartment->owner ?: $apartment->tenant;

            return $apartment;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'apartments_condominium_id_number_unique') !== false) {
                $existingQuery = Apartment::where('condominium_id', $condoId)
                    ->where('number', $validated['number']);

                if ($request->block_id) {
                    $existingQuery->where('block_id', $request->block_id);
                } elseif ($request->tower_id) {
                    $existingQuery->where('tower_id', $request->tower_id);
                }

                if ($existingQuery->exists()) {
                    return response()->json([
                        'message' => 'O número do apartamento já existe no bloco/torre selecionado.',
                        'errors' => [
                            'number' => ['O número do apartamento já existe no bloco/torre selecionado.'],
                        ],
                    ], 422);
                }

                return response()->json([
                    'message' => 'O número do apartamento já existe no condomínio.',
                    'errors' => [
                        'number' => ['O número do apartamento já existe no condomínio.'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function show(Apartment $apartment)
    {
        $apartment->load(['block', 'tower', 'owner', 'tenant', 'residents']);
        $apartment->resident = $apartment->owner ?: $apartment->tenant;

        return $apartment;
    }

    public function update(Request $request, Apartment $apartment)
    {
        $condoId = $apartment->condominium_id;

        $validated = $request->validate([
            'number' => [
                'string',
                'max:10',
                Rule::unique('apartments')->where(function ($query) use ($request, $condoId, $apartment) {
                    $query->where('condominium_id', $condoId)
                        ->where('id', '!=', $apartment->id);

                    if ($request->block_id) {
                        $query->where('block_id', $request->block_id);
                    }

                    if ($request->tower_id && ! $request->block_id) {
                        $query->where('tower_id', $request->tower_id);
                    }

                    return $query;
                }),
            ],
            'floor' => 'integer',
            'tower_id' => [
                'nullable',
                Rule::exists('towers', 'id')->where(fn ($q) => $q->where('condominium_id', $condoId)->where('active', true)),
            ],
            'block_id' => [
                'nullable',
                Rule::exists('blocks', 'id')->where(fn ($q) => $q->where('condominium_id', $condoId)->where('active', true)),
            ],
            'owner_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(fn ($q) => $q->where('active', true)->where('condominium_id', $condoId)),
            ],
            'tenant_id' => [
                'nullable',
                Rule::exists('residents', 'id')->where(fn ($q) => $q->where('active', true)->where('condominium_id', $condoId)),
            ],
            'active' => 'boolean',
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

        try {
            unset($validated['condominium_id']);
            $apartment->update($validated);
            $apartment->load(['owner', 'tenant', 'residents', 'block', 'tower']);
            $apartment->resident = $apartment->owner ?: $apartment->tenant;

            return $apartment;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'apartments_condominium_id_number_unique') !== false) {
                return response()->json([
                    'message' => 'O número do apartamento já existe no condomínio.',
                    'errors' => [
                        'number' => ['O número do apartamento já existe no condomínio.'],
                    ],
                ], 422);
            }

            throw $e;
        }
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
                        ->where('condominium_id', $apartment->condominium_id);
                }),
            ],
        ]);

        $apartment->residents()->attach($validated['resident_id'], [
            'type' => 'resident',
            'active' => true,
        ]);

        return $apartment->load(['residents']);
    }

    public function removeResident(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'resident_id' => [
                'required',
                Rule::exists('residents', 'id'),
            ],
        ]);

        $apartment->residents()->wherePivot('type', 'resident')->detach($validated['resident_id']);

        return $apartment->load(['residents']);
    }
}
