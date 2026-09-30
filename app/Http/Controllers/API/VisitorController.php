<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Condominium;

class VisitorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $visitors = Visitor::with('vehicles')->orderBy('name')->get();

        return response()->json($visitors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'User not authenticated or not associated with a condominium.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'document' => 'required|string|max:100|unique:visitors,document,NULL,id,condominium_id,' . $condominiumId,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_blocked' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['condominium_id'] = $condominiumId;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('visitors_photos', 'public');
            $data['photo_url'] = Storage::url($path);
        }

        unset($data['photo']);

        $visitor = Visitor::create($data);

        return response()->json($visitor, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $condominiumId = $request->query('condominium_id');
        if (!$condominiumId) {
            $user = auth()->user();
            if (!$user || !$user->condominium_id) {
                return response()->json(['message' => 'Condominium not specified.'], 400);
            }
            $condominiumId = $user->condominium_id;
        }

        $visitor = Visitor::where('condominium_id', $condominiumId)->with('vehicles')->find($id);

        if (!$visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }
        return response()->json($visitor);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'User not authenticated or not associated with a condominium.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $visitor = Visitor::where('condominium_id', $condominiumId)->find($id);

        if (!$visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'document' => 'sometimes|required|string|max:100|unique:visitors,document,' . $visitor->id . ',id,condominium_id,' . $condominiumId,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_blocked' => 'sometimes|boolean',
            '_method' => 'sometimes|string|in:PUT,PATCH',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('photo')) {
            if ($visitor->photo_url) {
                $oldPath = str_replace(Storage::url(''), '', $visitor->photo_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('photo')->store('visitors_photos', 'public');
            $data['photo_url'] = Storage::url($path);
        } elseif ($request->input('photo_url') === null && $visitor->photo_url) {
            $oldPath = str_replace(Storage::url(''), '', $visitor->photo_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            $data['photo_url'] = null;
        }

        unset($data['photo']);

        $visitor->update($data);

        return response()->json($visitor);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = auth()->user();
        $condominiumId = $user->condominium_id;

        $visitor = Visitor::where('condominium_id', $condominiumId)->find($id);

        if (!$visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        if ($visitor->photo_url) {
            $oldPath = str_replace(Storage::url(''), '', $visitor->photo_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $visitor->delete();

        return response()->json(null, 204);
    }

    /**
     * Search for a visitor by document.
     */
    public function searchByDocument(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'User not authenticated or not associated with a condominium.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $validator = Validator::make($request->all(), [
            'document' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $document = $request->input('document');
        $visitor = Visitor::where('condominium_id', $condominiumId)
            ->where('document', $document)
            ->with('vehicles')
            ->first();

        if (!$visitor) {
            return response()->json(['message' => 'Visitor not found.'], 404);
        }

        return response()->json($visitor);
    }
} 