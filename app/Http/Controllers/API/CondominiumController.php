<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\AuthorizesCondominiumAccess;
use App\Http\Controllers\Controller;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CondominiumController extends Controller
{
    use AuthorizesCondominiumAccess;

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isPlatformAdmin()) {
            return Condominium::query()->get();
        }

        return $user->condominiums()->get();
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

    public function show(Request $request, Condominium $condominium)
    {
        if ($response = $this->ensureCondominiumMembership($request, $condominium)) {
            return $response;
        }

        return $condominium->loadCount(['residents', 'apartments', 'parkingSpots']);
    }

    public function update(Request $request, Condominium $condominium)
    {
        if ($response = $this->ensureCondominiumMembership($request, $condominium)) {
            return $response;
        }

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

    public function destroy(Request $request, Condominium $condominium)
    {
        if ($response = $this->ensureCondominiumMembership($request, $condominium)) {
            return $response;
        }

        $condominium->delete();

        return response()->json(null, 204);
    }

    public function stats(Request $request, Condominium $condominium)
    {
        if ($response = $this->ensureCondominiumMembership($request, $condominium)) {
            return $response;
        }

        $blocks = $condominium->blocks()->withCount('apartments')->get();

        return response()->json($blocks);
    }
}
