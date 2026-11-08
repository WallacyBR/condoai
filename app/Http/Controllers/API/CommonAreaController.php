<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CommonArea;
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

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rules' => 'nullable|string',
            'photo_path' => 'nullable|string|max:255',
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id')->where(function ($q) {
                    return $q->where('active', true);
                })
            ],
            'active' => 'boolean'
        ]);

        return CommonArea::create($validated);
    }

    public function show(CommonArea $commonArea)
    {
        return $commonArea->load('bookings');
    }

    public function update(Request $request, CommonArea $commonArea)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'rules' => 'nullable|string',
            'photo_path' => 'nullable|string|max:255',
            'condominium_id' => [
                Rule::exists('condominiums', 'id')->where(function ($q) {
                    return $q->where('active', true);
                })
            ],
            'active' => 'boolean'
        ]);

        $commonArea->update($validated);
        return $commonArea;
    }

    public function destroy(CommonArea $commonArea)
    {
        $commonArea->delete();
        return response()->json(null, 204);
    }
}
