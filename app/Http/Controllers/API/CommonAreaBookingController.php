<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CommonAreaBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommonAreaBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = CommonAreaBooking::with(['commonArea', 'apartment']);

        if ($request->has('common_area_id')) {
            $query->where('common_area_id', $request->common_area_id);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'common_area_id' => ['required', Rule::exists('common_areas', 'id')],
            'apartment_id' => ['required', Rule::exists('apartments', 'id')],
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at'
        ]);

        $conflict = CommonAreaBooking::where('common_area_id', $validated['common_area_id'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_at', [$validated['start_at'], $validated['end_at']])
                  ->orWhereBetween('end_at', [$validated['start_at'], $validated['end_at']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_at', '<=', $validated['start_at'])
                         ->where('end_at', '>=', $validated['end_at']);
                  });
            })
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'Horário indisponível'], 422);
        }

        return CommonAreaBooking::create($validated);
    }

    public function show(CommonAreaBooking $commonAreaBooking)
    {
        return $commonAreaBooking->load(['commonArea', 'apartment']);
    }

    public function destroy(CommonAreaBooking $commonAreaBooking)
    {
        $commonAreaBooking->delete();
        return response()->json(null, 204);
    }
}
