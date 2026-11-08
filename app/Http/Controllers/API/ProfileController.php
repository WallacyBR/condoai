<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Profile::with('modules')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:profiles',
            'description' => 'nullable|string|max:255',
            'modules' => 'array',
            'modules.*.id' => 'exists:modules,id',
            'modules.*.can_view' => 'boolean',
            'modules.*.can_create' => 'boolean',
            'modules.*.can_update' => 'boolean',
            'modules.*.can_delete' => 'boolean',
            'modules.*.scope' => 'in:own,condominium,all',
        ]);

        $modules = collect($validated['modules'] ?? [])
            ->mapWithKeys(function ($m) {
                $id = $m['id'];
                unset($m['id']);
                return [$id => [
                    'can_view' => $m['can_view'] ?? false,
                    'can_create' => $m['can_create'] ?? false,
                    'can_update' => $m['can_update'] ?? false,
                    'can_delete' => $m['can_delete'] ?? false,
                    'scope' => $m['scope'] ?? 'own',
                ]];
            })->toArray();
        unset($validated['modules']);

        $profile = Profile::create($validated);

        if ($profile->name === 'SysOp') {
            $modules = Module::all()->mapWithKeys(function ($m) {
                return [$m->id => [
                    'can_view' => true,
                    'can_create' => true,
                    'can_update' => true,
                    'can_delete' => true,
                    'scope' => 'all',
                ]];
            })->toArray();
        }

        $profile->modules()->sync($modules);

        return $profile->load('modules');
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        return $profile->load('modules');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255', Rule::unique('profiles')->ignore($profile->id)],
            'description' => 'nullable|string|max:255',
            'modules' => 'array',
            'modules.*.id' => 'exists:modules,id',
            'modules.*.can_view' => 'boolean',
            'modules.*.can_create' => 'boolean',
            'modules.*.can_update' => 'boolean',
            'modules.*.can_delete' => 'boolean',
            'modules.*.scope' => 'in:own,condominium,all',
        ]);

        $modules = collect($validated['modules'] ?? [])
            ->mapWithKeys(function ($m) {
                $id = $m['id'];
                unset($m['id']);
                return [$id => [
                    'can_view' => $m['can_view'] ?? false,
                    'can_create' => $m['can_create'] ?? false,
                    'can_update' => $m['can_update'] ?? false,
                    'can_delete' => $m['can_delete'] ?? false,
                    'scope' => $m['scope'] ?? 'own',
                ]];
            })->toArray();
        unset($validated['modules']);

        $profile->update($validated);

        if ($profile->name === 'SysOp') {
            $modules = Module::all()->mapWithKeys(function ($m) {
                return [$m->id => [
                    'can_view' => true,
                    'can_create' => true,
                    'can_update' => true,
                    'can_delete' => true,
                    'scope' => 'all',
                ]];
            })->toArray();
        }

        if (!empty($modules)) {
            $profile->modules()->sync($modules);
        }

        return $profile->load('modules');
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
