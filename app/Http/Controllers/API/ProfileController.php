<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profile;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Profile::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:profiles',
            'description' => 'nullable|string|max:255',
        ]);

        return Profile::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        return $profile;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255', Rule::unique('profiles')->ignore($profile->id)],
            'description' => 'nullable|string|max:255',
        ]);

        $profile->update($validated);
        return $profile;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile)
    {
        $profile->delete();
        return response()->json(null, 204);
    }
}
