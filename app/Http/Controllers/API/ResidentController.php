<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    public function index()
    {
        return Resident::with('condominium')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'apartment' => 'required|string|max:10',
            'block' => 'required|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:residents',
            'active' => 'boolean',
            'condominium_id' => 'required|exists:condominiums,id'
        ]);

        return Resident::create($validated);
    }

    public function show(Resident $resident)
    {
        return $resident->load('condominium');
    }

    public function update(Request $request, Resident $resident)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'apartment' => 'string|max:10',
            'block' => 'string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => ['email', Rule::unique('residents')->ignore($resident->id)],
            'active' => 'boolean',
            'condominium_id' => 'exists:condominiums,id'
        ]);

        $resident->update($validated);
        return $resident;
    }

    public function destroy(Resident $resident)
    {
        $resident->delete();
        return response()->json(null, 204);
    }
}
