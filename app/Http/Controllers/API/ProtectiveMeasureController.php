<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\ProtectiveMeasure;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProtectiveMeasureController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        $measures = ProtectiveMeasure::query()
            ->with(['protectedResident:id,name', 'aggressorVisitor:id,name,document'])
            ->orderBy('validity_start_date', 'desc')
            ->get();

        return response()->json($measures);
    }

    public function store(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $validator = Validator::make($request->all(), [
            'protected_resident_id' => 'required|exists:users,id',
            'aggressor_visitor_id' => 'required|exists:visitors,id',
            'process_number' => 'nullable|string|max:100',
            'measure_description' => 'required|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'validity_start_date' => 'required|date',
            'validity_end_date' => 'nullable|date|after_or_equal:validity_start_date',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        unset($data['condominium_id']);
        $data['condominium_id'] = $condominiumId;

        $resident = User::where('id', $data['protected_resident_id'])->where('condominium_id', $condominiumId)->first();
        if (! $resident) {
            return response()->json(['message' => 'Protected resident not found in this condominium.'], 422);
        }
        $visitor = Visitor::where('id', $data['aggressor_visitor_id'])->where('condominium_id', $condominiumId)->first();
        if (! $visitor) {
            return response()->json(['message' => 'Aggressor visitor not found in this condominium.'], 422);
        }

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('protective_measures_docs', 'public');
            $data['document_url'] = Storage::url($path);
        }
        unset($data['document']);

        $measure = ProtectiveMeasure::create($data);

        return response()->json($measure, 201);
    }

    public function show(string $id)
    {
        $measure = ProtectiveMeasure::query()
            ->with(['protectedResident:id,name', 'aggressorVisitor:id,name,document'])
            ->find($id);

        if (! $measure) {
            return response()->json(['message' => 'Protective measure not found.'], 404);
        }

        return response()->json($measure);
    }

    public function update(Request $request, string $id)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $measure = ProtectiveMeasure::find($id);

        if (! $measure) {
            return response()->json(['message' => 'Protective measure not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'protected_resident_id' => 'sometimes|required|exists:users,id',
            'aggressor_visitor_id' => 'sometimes|required|exists:visitors,id',
            'process_number' => 'nullable|string|max:100',
            'measure_description' => 'sometimes|required|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'validity_start_date' => 'sometimes|required|date',
            'validity_end_date' => 'nullable|date|after_or_equal:validity_start_date',
            'is_active' => 'sometimes|boolean',
            '_method' => 'sometimes|string|in:PUT,PATCH',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        if (isset($data['protected_resident_id'])) {
            $resident = User::where('id', $data['protected_resident_id'])->where('condominium_id', $condominiumId)->first();
            if (! $resident) {
                return response()->json(['message' => 'Protected resident not found in this condominium.'], 422);
            }
        }
        if (isset($data['aggressor_visitor_id'])) {
            $visitor = Visitor::where('id', $data['aggressor_visitor_id'])->where('condominium_id', $condominiumId)->first();
            if (! $visitor) {
                return response()->json(['message' => 'Aggressor visitor not found in this condominium.'], 422);
            }
        }

        if ($request->hasFile('document')) {
            if ($measure->document_url) {
                $oldPath = str_replace(Storage::url(''), '', $measure->document_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('document')->store('protective_measures_docs', 'public');
            $data['document_url'] = Storage::url($path);
        } elseif ($request->input('document_url') === null && $measure->document_url) {
            $oldPath = str_replace(Storage::url(''), '', $measure->document_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            $data['document_url'] = null;
        }
        unset($data['document'], $data['condominium_id']);

        $measure->update($data);

        return response()->json($measure);
    }

    public function destroy(string $id)
    {
        $measure = ProtectiveMeasure::find($id);

        if (! $measure) {
            return response()->json(['message' => 'Protective measure not found.'], 404);
        }

        if ($measure->document_url) {
            $oldPath = str_replace(Storage::url(''), '', $measure->document_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $measure->delete();

        return response()->json(null, 204);
    }
}
