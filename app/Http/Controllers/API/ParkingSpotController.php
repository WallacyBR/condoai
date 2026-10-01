<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
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
        $condoId = $this->currentCondominiumId($request);

        $validated = $request->validate([
            'identifier' => [
                'required', 'string', 'max:10',
                Rule::unique('parking_spots')->where(fn ($q) => $q->where('condominium_id', $condoId)),
            ],
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')->where(fn ($q) => $q->where('condominium_id', $condoId)),
            ],
            'rented_to_apartment_id' => [
                'nullable',
                Rule::exists('apartments', 'id')->where(fn ($q) => $q->where('condominium_id', $condoId)),
            ],
        ]);

        $validated['condominium_id'] = $condoId;

        $spot = ParkingSpot::create($validated);

        return response()->json($spot->load(['apartment', 'rentedToApartment']), 201);
    }

    public function batchStore(Request $request)
    {
        $condoId = $this->currentCondominiumId($request);

        $validated = $request->validate([
            'spots_per_apartment' => 'required|integer|min:1',
        ]);

        $apartments = Apartment::where('condominium_id', $condoId)->get();
        $spots = [];

        foreach ($apartments as $apartment) {
            for ($i = 1; $i <= $validated['spots_per_apartment']; $i++) {
                $identifier = "{$apartment->number}-V{$i}";

                // Check for existing spot to avoid duplicates
                if (ParkingSpot::where('identifier', $identifier)->where('condominium_id', $condoId)->exists()) {
                    continue;
                }

                $spots[] = ParkingSpot::create([
                    'identifier' => $identifier,
                    'condominium_id' => $condoId,
                    'apartment_id' => $apartment->id,
                ]);
            }
        }

        return response()->json($spots, 201);
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
                Rule::unique('parking_spots')->where(fn ($q) => $q->where('condominium_id', $condoId)->where('id', '!=', $parkingSpot->id)),
            ],
            'apartment_id' => [
                'sometimes',
                Rule::exists('apartments', 'id')->where(fn ($q) => $q->where('condominium_id', $condoId)),
            ],
            'rented_to_apartment_id' => [
                'nullable',
                Rule::exists('apartments', 'id')->where(fn ($q) => $q->where('condominium_id', $condoId)),
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
                Rule::exists('apartments', 'id')->where(fn ($q) => $q->where('condominium_id', $condoId)),
            ],
        ]);
        $parkingSpot->update(['rented_to_apartment_id' => $validated['rented_to_apartment_id'] ?? null]);

        return $parkingSpot->load(['apartment', 'rentedToApartment']);
    }

    private function currentCondominiumId(Request $request): int
    {
        if (app()->bound('currentCondominium')) {
            return app('currentCondominium')->id;
        }

        if ($request->attributes->has('condominium')) {
            return $request->attributes->get('condominium')->id;
        }

        return (int) $request->user()->condominium_id;
    }
}
