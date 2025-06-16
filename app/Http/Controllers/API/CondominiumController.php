<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CondominiumController extends Controller
{
    public function index()
    {
        return Condominium::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'required|string|unique:condominiums',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:2',
            'zip_code' => 'required|string|max:9',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:condominiums',
            'responsible' => 'required|string|max:255',
            'domain' => 'required|string|unique:condominiums|max:100',
            'active' => 'boolean',
        ]);

        return Condominium::create($validated);
    }

    public function show(Condominium $condominium)
    {
        return $condominium->load('residents');
    }

    public function update(Request $request, Condominium $condominium)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'document' => ['string', Rule::unique('condominiums')->ignore($condominium->id)],
            'address' => 'string|max:255',
            'city' => 'string|max:100',
            'state' => 'string|max:2',
            'zip_code' => 'string|max:9',
            'phone' => 'nullable|string|max:20',
            'email' => ['email', Rule::unique('condominiums')->ignore($condominium->id)],
            'responsible' => 'string|max:255',
            'domain' => ['string', Rule::unique('condominiums')->ignore($condominium->id), 'max:100'],
            'active' => 'boolean',
        ]);

        $condominium->update($validated);

        return $condominium;
    }

    public function destroy(Condominium $condominium)
    {
        $condominium->delete();

        return response()->json(null, 204);
    }
}
