<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    public function index()
    {
        return Package::with('apartment')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => ['required', Rule::exists('apartments', 'id')],
            'photo' => 'nullable|string',
        ]);

        $package = Package::create($validated);

        return response()->json($package->load('apartment'), 201);
    }

    public function show(string $id)
    {
        return Package::with('apartment')->findOrFail($id);
    }

    public function withdraw(Request $request, Package $package)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        if ($package->code !== $validated['code']) {
            return response()->json(['message' => 'Código incorreto'], 422);
        }

        $package->update(['retrieved_at' => now()]);

        return $package->refresh()->load('apartment');
    }
}
