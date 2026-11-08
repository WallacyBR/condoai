<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CommonArea;
use App\Models\CommonAreaBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommonAreaController extends Controller
{
    public function index(Request $request)
    {
        $query = CommonArea::query();

        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }

        return $query->with('bookings')->get();
    }

    public function store(Request $request)
    {
        $condoId = $request->condominium_id;
        $validated = $request->validate([
            'name' => [
                'required', 'string',
                Rule::unique('common_areas')->where(fn ($q) => $q->where('condominium_id', $condoId)),
            ],
            'description' => 'nullable|string',
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id')->where(fn ($q) => $q->where('active', true)),
            ],
            'capacity' => 'integer|min:0',
        ]);

        $area = CommonArea::create($validated);

        return response()->json($area, 201);
    }

    public function show(CommonArea $commonArea)
    {
        return $commonArea->load('bookings');
    }

    public function update(Request $request, CommonArea $commonArea)
    {
        $condoId = $commonArea->condominium_id;
        $validated = $request->validate([
            'name' => [
                'string',
                Rule::unique('common_areas')->where(fn ($q) => $q->where('condominium_id', $condoId)->where('id', '!=', $commonArea->id)),
            ],
            'description' => 'nullable|string',
            'capacity' => 'integer|min:0',
        ]);

        $commonArea->update($validated);

        return $commonArea;
    }

    public function destroy(CommonArea $commonArea)
    {
        $commonArea->delete();

        return response()->json(null, 204);
    }

    public function bookings(CommonArea $commonArea)
    {
        return $commonArea->bookings()->with('apartment')->get();
    }

    public function book(Request $request, CommonArea $commonArea)
    {
        $validated = $request->validate([
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')->where(fn ($q) => $q->where('condominium_id', $commonArea->condominium_id)),
            ],
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $overlap = $commonArea->bookings()
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($qq) use ($validated) {
                        $qq->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                    });
            })->exists();

        if ($overlap) {
            return response()->json(['message' => 'Horário indisponível'], 422);
        }

        $booking = $commonArea->bookings()->create($validated);

        return response()->json($booking->load('apartment'), 201);
    }
}
