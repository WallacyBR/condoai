<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VisitorVehicle;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitorVehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $visitorId)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'User not authenticated or not associated with a condominium.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $visitor = Visitor::where('condominium_id', $condominiumId)->find($visitorId);
        if (!$visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        return response()->json($visitor->vehicles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $visitorId)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'User not authenticated or not associated with a condominium.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $visitor = Visitor::where('condominium_id', $condominiumId)->find($visitorId);
        if (!$visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'license_plate' => 'required|string|max:15|unique:visitor_vehicles,license_plate,NULL,id,condominium_id,' . $condominiumId,
            'model' => 'required|string|max:100',
            'color' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['visitor_id'] = $visitor->id;
        $data['condominium_id'] = $condominiumId;

        $vehicle = VisitorVehicle::create($data);

        return response()->json($vehicle, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $visitorId, string $vehicleId)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'User not authenticated or not associated with a condominium.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $visitor = Visitor::where('condominium_id', $condominiumId)->find($visitorId);
        if (!$visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        $vehicle = $visitor->vehicles()->where('id', $vehicleId)->where('condominium_id', $condominiumId)->first();
        if (!$vehicle) {
            return response()->json(['message' => 'Visitor vehicle not found.'], 404);
        }

        return response()->json($vehicle);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $visitorId, string $vehicleId)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'User not authenticated or not associated with a condominium.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $visitor = Visitor::where('condominium_id', $condominiumId)->find($visitorId);
        if (!$visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        $vehicle = $visitor->vehicles()->where('id', $vehicleId)->where('condominium_id', $condominiumId)->first();
        if (!$vehicle) {
            return response()->json(['message' => 'Visitor vehicle not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'license_plate' => 'sometimes|required|string|max:15|unique:visitor_vehicles,license_plate,' . $vehicle->id . ',id,condominium_id,' . $condominiumId,
            'model' => 'sometimes|required|string|max:100',
            'color' => 'sometimes|required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $vehicle->update($validator->validated());

        return response()->json($vehicle);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $visitorId, string $vehicleId)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'User not authenticated or not associated with a condominium.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $visitor = Visitor::where('condominium_id', $condominiumId)->find($visitorId);
        if (!$visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        $vehicle = $visitor->vehicles()->where('id', $vehicleId)->where('condominium_id', $condominiumId)->first();
        if (!$vehicle) {
            return response()->json(['message' => 'Visitor vehicle not found.'], 404);
        }

        $vehicle->delete();

        return response()->json(null, 204);
    }
} 