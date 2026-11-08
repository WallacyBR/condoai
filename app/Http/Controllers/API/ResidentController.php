<?php

namespace App\Http\Controllers\API;

use App\Enums\ResidentClassification;
use App\Http\Controllers\Controller;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Resident::with('condominium');

        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rg' => 'nullable|string|max:30',
            'cpf' => 'required|string|max:20',
            'cell_phone' => 'nullable|string|max:20',
            'commercial_phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'required|email|unique:residents,email',
            'person_type' => ['required', Rule::in(['fisica','juridica'])],
            'photo' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_porter' => 'boolean',
            'is_janitor' => 'boolean',
            'is_cleaning' => 'boolean',
            'apartment_id' => ['nullable', Rule::exists('apartments','id')],
            'billing_address_type' => 'required|in:condominium,custom',
            'cep' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'classification' => ['required', Rule::in([ 'proprietario', 'morador', 'inquilino' ])],
            'active' => 'boolean',
            'condominium_id' => 'required|integer|exists:condominiums,id',
        ]);

        // Regra: proprietário não pode ser inquilino
        if (($request->classification === 'proprietario' && $request->classification === 'inquilino')) {
            return response()->json(['errors' => ['classification' => 'Proprietário não pode ser inquilino']], 422);
        }

        $resident = Resident::create($validated);

        return response()->json($resident, 201);
    }

    public function show(Resident $resident)
    {
        return $resident->load('condominium');
    }

    public function update(Request $request, Resident $resident)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'rg' => 'nullable|string|max:30',
            'cpf' => 'sometimes|string|max:20',
            'cell_phone' => 'nullable|string|max:20',
            'commercial_phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => ['sometimes','email', Rule::unique('residents')->ignore($resident->id)],
            'person_type' => ['sometimes', Rule::in(['fisica','juridica'])],
            'photo' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_porter' => 'boolean',
            'is_janitor' => 'boolean',
            'is_cleaning' => 'boolean',
            'apartment_id' => ['nullable', Rule::exists('apartments','id')],
            'billing_address_type' => 'sometimes|in:condominium,custom',
            'cep' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'classification' => ['sometimes', Rule::in([ 'proprietario', 'morador', 'inquilino' ])],
            'active' => 'boolean',
            'condominium_id' => 'sometimes|integer|exists:condominiums,id',
        ]);

        // Regra: proprietário não pode ser inquilino
        if (($request->classification === 'proprietario' && $request->classification === 'inquilino')) {
            return response()->json(['errors' => ['classification' => 'Proprietário não pode ser inquilino']], 422);
        }

        $resident->update($validated);

        return $resident->refresh();
    }

    public function destroy(Resident $resident)
    {
        $resident->delete();

        return response()->json(null, 204);
    }
}
