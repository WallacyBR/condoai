<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PetController extends Controller
{
    public function index(Request $request)
    {
        $query = Pet::query();

        if ($request->has('apartment_id')) {
            $query->where('apartment_id', $request->apartment_id);
        }

        return $query->with('apartment')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:50',
            'breed' => 'required|string|max:255',
            'photo' => 'nullable|string',
            'apartment_id' => ['required', Rule::exists('apartments', 'id')],
        ]);

        $pet = Pet::create($validated);

        return response()->json($pet->load('apartment'), 201);
    }

    public function show(Pet $pet)
    {
        return $pet->load('apartment');
    }

    public function update(Request $request, Pet $pet)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'species' => 'string|max:50',
            'breed' => 'string|max:255',
            'photo' => 'nullable|string',
            'apartment_id' => ['sometimes', Rule::exists('apartments', 'id')],
        ]);

        $pet->update($validated);

        return $pet->load('apartment');
    }

    public function destroy(Pet $pet)
    {
        $pet->delete();

        return response()->json(null, 204);
    }
}
