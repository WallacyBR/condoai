<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::query();

        if ($request->has('condominium_id')) {
            $query->whereHas('apartment', function ($q) use ($request) {
                $q->where('condominium_id', $request->condominium_id);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNull('retrieved_at');
            } elseif ($request->status === 'retrieved') {
                $query->whereNotNull('retrieved_at');
            }
        }

        if ($request->filled('unit')) {
            $search = $request->unit;
            $query->whereHas('apartment', function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%");
            });
        }

        return $query->with('apartment')->orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => ['required', Rule::exists('apartments', 'id')],
            'photo' => 'nullable|string',
            'pickup_location' => 'required|string',
            'observation' => 'nullable|string',
        ]);

        $data = $validated;
        $data['user_id'] = $request->user()->id;

        $package = Package::create($data);

        return response()->json($package->load('apartment'), 201);
    }

    public function show(string $id)
    {
        return Package::with('apartment')->findOrFail($id);
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'apartment_id' => ['sometimes', Rule::exists('apartments', 'id')],
            'photo' => 'sometimes|nullable|string',
            'pickup_location' => 'sometimes|string',
            'observation' => 'sometimes|nullable|string',
        ]);

        $package->update($validated);

        return $package->refresh()->load('apartment');
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return response()->json(null, 204);
    }

    public function withdraw(Request $request, Package $package)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'signature' => 'nullable|string',
            'document_photo' => 'nullable|string',
        ]);

        if ($package->code !== $validated['code']) {
            return response()->json(['message' => 'Código incorreto'], 422);
        }

        $data = [
            'retrieved_at' => now(),
        ];

        if (! empty($validated['signature'])) {
            $data['signature'] = $validated['signature'];
        }

        if (! empty($validated['document_photo'])) {
            $data['document_photo'] = $validated['document_photo'];
        }

        $package->update($data);

        return $package->refresh()->load('apartment');
    }
}
