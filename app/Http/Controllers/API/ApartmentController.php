<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
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

        if ($request->has('tower_id')) {
            $query->where('tower_id', $request->tower_id);
        }

        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }

        return $query->with(['owner', 'tenant', 'residents', 'block', 'tower'])->orderByFieldSmart('number')->get();
    }

    public function store(Request $request)
    {
        $condoId = $request->condominium_id;

        $validated = $request->validate([
            'number' => [
                'required',
                'string',
                'max:10',
                Rule::unique('apartments')->where(function ($query) use ($request, $condoId) {
                    $query->where('condominium_id', $condoId);
                    
                    // Se um bloco foi especificado, verifica unicidade apenas dentro do bloco
                    if ($request->block_id) {
                        $query->where('block_id', $request->block_id);
                    }
                    
                    // Se uma torre foi especificada (sem bloco), verifica unicidade apenas dentro da torre
                    if ($request->tower_id && !$request->block_id) {
                        $query->where('tower_id', $request->tower_id);
                    }
                    
                    return $query;
                }),
            ],
            'floor' => 'required|integer',
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id')->where(fn ($q) => $q->where('active', true)),
            ],
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
            $apartment = Apartment::create($validated);

            if (! empty($validated['owner_id'])) {
                $apartment->residents()->attach($validated['owner_id'], ['type' => 'owner']);
            }

            if (! empty($validated['tenant_id'])) {
                $apartment->residents()->attach($validated['tenant_id'], ['type' => 'tenant']);
            }

            return $apartment->load(['owner', 'tenant', 'residents', 'block', 'tower']);
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar erro de constraint violation
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'apartments_condominium_id_number_unique') !== false) {
                // Verificar se o número já existe no mesmo bloco/torre
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
                            'number' => ['O número do apartamento já existe no bloco/torre selecionado.']
                        ]
                    ], 422);
                } else {
                    return response()->json([
                        'message' => 'O número do apartamento já existe no condomínio.',
                        'errors' => [
                            'number' => ['O número do apartamento já existe no condomínio.']
                        ]
                    ], 422);
                }
            }
            
            // Re-throw outras exceções
            throw $e;
        }
    }

    public function show(Apartment $apartment)
    {
        return $apartment->load(['block', 'tower', 'owner', 'tenant', 'residents']);
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
                    
                    // Se um bloco foi especificado, verifica unicidade apenas dentro do bloco
                    if ($request->block_id) {
                        $query->where('block_id', $request->block_id);
                    }
                    
                    // Se uma torre foi especificada (sem bloco), verifica unicidade apenas dentro da torre
                    if ($request->tower_id && !$request->block_id) {
                        $query->where('tower_id', $request->tower_id);
                    }
                    
                    return $query;
                }),
            ],
            'floor' => 'integer',
            'condominium_id' => [
                Rule::exists('condominiums', 'id')->where(fn ($q) => $q->where('active', true)),
            ],
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
            $apartment->update($validated);
            return $apartment->load(['owner', 'tenant', 'residents', 'block', 'tower']);
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar erro de constraint violation
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'apartments_condominium_id_number_unique') !== false) {
                // Verificar se o número já existe no mesmo bloco/torre
                $existingQuery = Apartment::where('condominium_id', $condoId)
                    ->where('number', $validated['number'])
                    ->where('id', '!=', $apartment->id);
                
                if ($request->block_id) {
                    $existingQuery->where('block_id', $request->block_id);
                } elseif ($request->tower_id) {
                    $existingQuery->where('tower_id', $request->tower_id);
                }
                
                if ($existingQuery->exists()) {
                    return response()->json([
                        'message' => 'O número do apartamento já existe no bloco/torre selecionado.',
                        'errors' => [
                            'number' => ['O número do apartamento já existe no bloco/torre selecionado.']
                        ]
                    ], 422);
                } else {
                    return response()->json([
                        'message' => 'O número do apartamento já existe no condomínio.',
                        'errors' => [
                            'number' => ['O número do apartamento já existe no condomínio.']
                        ]
                    ], 422);
                }
            }
            
            // Re-throw outras exceções
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
