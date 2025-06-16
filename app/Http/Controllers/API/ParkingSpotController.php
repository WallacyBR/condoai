<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ParkingSpot;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParkingSpotController extends Controller
{
    public function index(Request $request)
    {
        $query = ParkingSpot::query();

        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }

        return $query->with(['apartment', 'rentedToApartment'])->get();
    }

    public function store(Request $request)
    {
        $condoId = $request->condominium_id;

        $validated = $request->validate([
            'identifier' => [
                'required', 'string', 'max:10',
                Rule::unique('parking_spots')->where(fn($q) => $q->where('condominium_id', $condoId)),
            ],
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id')->where(fn($q) => $q->where('active', true)),
            ],
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')->where(fn($q) => $q->where('condominium_id', $condoId)),
            ],
            'rented_to_apartment_id' => [
                'nullable',
                Rule::exists('apartments', 'id')->where(fn($q) => $q->where('condominium_id', $condoId)),
            ],
        ]);

        $spot = ParkingSpot::create($validated);
        return response()->json($spot->load(['apartment', 'rentedToApartment']), 201);
    }

    public function show(ParkingSpot $parkingSpot)
    {
        return $parkingSpot->load(['apartment', 'rentedToApartment', 'condominium']);
    }

    public function update(Request $request, ParkingSpot $parkingSpot)
    {
        $condoId = $parkingSpot->condominium_id;
        $validated = $request->validate([
            'identifier' => [
                'string', 'max:10',
                Rule::unique('parking_spots')->where(fn($q) => $q->where('condominium_id', $condoId)->where('id', '!=', $parkingSpot->id)),
            ],
            'apartment_id' => [
                'sometimes',
                Rule::exists('apartments', 'id')->where(fn($q) => $q->where('condominium_id', $condoId)),
            ],
            'rented_to_apartment_id' => [
                'nullable',
                Rule::exists('apartments', 'id')->where(fn($q) => $q->where('condominium_id', $condoId)),
            ],
        ]);

        $parkingSpot->update($validated);
        return $parkingSpot->load(['apartment', 'rentedToApartment']);
    }

    public function destroy(ParkingSpot $parkingSpot)
    {
        $parkingSpot->delete();
        return response()->json(null, 204);
    }

    public function rent(Request $request, ParkingSpot $parkingSpot)
    {
        $condoId = $parkingSpot->condominium_id;
        $validated = $request->validate([
            'rented_to_apartment_id' => [
                'nullable',
                Rule::exists('apartments', 'id')->where(fn($q) => $q->where('condominium_id', $condoId)),
            ],
        ]);
        $parkingSpot->update(['rented_to_apartment_id' => $validated['rented_to_apartment_id'] ?? null]);
        return $parkingSpot->load(['apartment', 'rentedToApartment']);
    }
}
