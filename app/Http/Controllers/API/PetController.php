<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PetController extends Controller
{
    public function index()
    {
        return Pet::with('apartment')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:100',
            'breed' => 'nullable|string|max:100',
            'photo_path' => 'nullable|string|max:255',
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id'),
            ],
        ]);

        return Pet::create($validated);
    }

    public function show(Pet $pet)
    {
        return $pet->load('apartment');
    }

    public function update(Request $request, Pet $pet)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'species' => 'string|max:100',
            'breed' => 'nullable|string|max:100',
            'photo_path' => 'nullable|string|max:255',
            'apartment_id' => [Rule::exists('apartments', 'id')],
        ]);

        $pet->update($validated);
        return $pet;
    }

    public function destroy(Pet $pet)
    {
        $pet->delete();
        return response()->json(null, 204);
    }
}
