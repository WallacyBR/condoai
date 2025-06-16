<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Module::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:modules',
            'description' => 'nullable|string|max:255',
        ]);

        return Module::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(Module $module)
    {
        return $module;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255', Rule::unique('modules')->ignore($module->id)],
            'description' => 'nullable|string|max:255',
        ]);

        $module->update($validated);
        return $module;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Module $module)
    {
        $module->delete();
        return response()->json(null, 204);
    }
}
