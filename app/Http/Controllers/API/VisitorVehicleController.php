<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Visitor;
use App\Models\VisitorVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitorVehicleController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request, string $visitorId)
    {
        $visitor = Visitor::find($visitorId);
        if (! $visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        return response()->json($visitor->vehicles);
    }

    public function store(Request $request, string $visitorId)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $visitor = Visitor::find($visitorId);
        if (! $visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'license_plate' => 'required|string|max:15|unique:visitor_vehicles,license_plate,NULL,id,condominium_id,'.$condominiumId,
            'model' => 'required|string|max:100',
            'color' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['visitor_id'] = $visitor->id;
        unset($data['condominium_id']);
        $data['condominium_id'] = $condominiumId;

        $vehicle = VisitorVehicle::create($data);

        return response()->json($vehicle, 201);
    }

    public function show(string $visitorId, string $vehicleId)
    {
        $visitor = Visitor::find($visitorId);
        if (! $visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        $vehicle = $visitor->vehicles()->where('id', $vehicleId)->first();
        if (! $vehicle) {
            return response()->json(['message' => 'Visitor vehicle not found.'], 404);
        }

        return response()->json($vehicle);
    }

    public function update(Request $request, string $visitorId, string $vehicleId)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $visitor = Visitor::find($visitorId);
        if (! $visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        $vehicle = $visitor->vehicles()->where('id', $vehicleId)->first();
        if (! $vehicle) {
            return response()->json(['message' => 'Visitor vehicle not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'license_plate' => 'sometimes|required|string|max:15|unique:visitor_vehicles,license_plate,'.$vehicle->id.',id,condominium_id,'.$condominiumId,
            'model' => 'sometimes|required|string|max:100',
            'color' => 'sometimes|required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $vehicle->update($validator->validated());

        return response()->json($vehicle);
    }

    public function destroy(string $visitorId, string $vehicleId)
    {
        $visitor = Visitor::find($visitorId);
        if (! $visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        $vehicle = $visitor->vehicles()->where('id', $vehicleId)->first();
        if (! $vehicle) {
            return response()->json(['message' => 'Visitor vehicle not found.'], 404);
        }

        $vehicle->delete();

        return response()->json(null, 204);
    }
}
