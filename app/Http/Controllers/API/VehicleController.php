<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query();

        if ($request->has('apartment_id')) {
            $query->where('apartment_id', $request->apartment_id);
        }

        if ($request->has('parking_spot_id')) {
            $query->where('parking_spot_id', $request->parking_spot_id);
        }

        return $query->with(['apartment', 'parkingSpot'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'license_plate' => 'required|string|max:10|unique:vehicles,license_plate',
            'model' => 'required|string|max:255',
            'color' => 'required|string|max:50',
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id'),
            ],
            'parking_spot_id' => [
                'nullable',
                Rule::exists('parking_spots', 'id'),
            ],
        ]);

        $vehicle = Vehicle::create($validated);

        return response()->json($vehicle->load(['apartment', 'parkingSpot']), 201);
    }

    public function show(Vehicle $vehicle)
    {
        return $vehicle->load(['apartment', 'parkingSpot']);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'license_plate' => [
                'string', 'max:10',
                Rule::unique('vehicles', 'license_plate')->ignore($vehicle->id),
            ],
            'model' => 'string|max:255',
            'color' => 'string|max:50',
            'apartment_id' => [
                'sometimes',
                Rule::exists('apartments', 'id'),
            ],
            'parking_spot_id' => [
                'nullable',
                Rule::exists('parking_spots', 'id'),
            ],
        ]);

        $vehicle->update($validated);

        return $vehicle->load(['apartment', 'parkingSpot']);
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json(null, 204);
    }
}
