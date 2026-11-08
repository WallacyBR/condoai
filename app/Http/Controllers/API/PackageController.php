<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::query();

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

        return $query->with(['apartment.tower', 'apartment.block'])->orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'photo' => 'nullable|string',
            'pickup_location' => 'required|string|max:255',
            'observation' => 'nullable|string',
        ]);

        $apartment = Apartment::find($validated['apartment_id']);
        if (!$apartment) {
            return response()->json(['message' => 'Apartamento não encontrado'], 404);
        }

        $data = $validated;
        $data['user_id'] = $request->user()->id;
        $data['condominium_id'] = $apartment->condominium_id;

        if ($request->photo) {
            try {
                $image = $request->photo;
                // Remove o cabeçalho do base64
                list($type, $image) = explode(';', $image);
                list(, $image) = explode(',', $image);
                $imageData = base64_decode($image);

                // Gera um nome de arquivo único
                $extension = str_replace('data:image/', '', $type);
                $filename = 'packages/' . Str::uuid() . '.' . $extension;

                // Salva a imagem no armazenamento público
                Storage::disk('public')->put($filename, $imageData);

                // Salva o caminho público no banco de dados
                $data['photo'] = Storage::url($filename);

            } catch (\Exception $e) {
                \Log::error('Erro ao processar a imagem da correspondência: ' . $e->getMessage());
                // Opcional: retornar um erro se o upload da imagem for crítico
                return response()->json(['message' => 'Erro ao processar a imagem.'], 500);
            }
        } else {
            $data['photo'] = null;
        }

        try {
            $package = Package::create($data);

            return response()->json($package->load(['apartment.tower', 'apartment.block']), 201);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar package', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            return response()->json(['message' => 'Erro interno do servidor'], 500);
        }
    }

    public function show(string $id)
    {
        return Package::with(['apartment.tower', 'apartment.block'])->findOrFail($id);
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'apartment_id' => ['sometimes', 'integer', Rule::exists('apartments', 'id')],
            'photo' => 'sometimes|nullable|string',
            'pickup_location' => 'sometimes|string|max:255',
            'observation' => 'sometimes|nullable|string',
        ]);

        // Log para depuração
        \Log::info('Dados recebidos no PackageController::update', [
            'package_id' => $package->id,
            'user_id' => $request->user()->id,
            'validated_data' => $validated,
            'all_request_data' => $request->all()
        ]);

        // Se apartment_id está sendo atualizado, verificar se existe
        if (isset($validated['apartment_id'])) {
            $apartment = \App\Models\Apartment::find($validated['apartment_id']);
            if (!$apartment) {
                \Log::error('Apartamento não encontrado para update', ['apartment_id' => $validated['apartment_id']]);
                return response()->json(['message' => 'Apartamento não encontrado'], 422);
            }
            
            \Log::info('Apartamento encontrado para update', [
                'apartment' => $apartment,
                'condominium_id' => $apartment->condominium_id
            ]);
        }

        try {
            $package->update($validated);
            \Log::info('Package atualizado com sucesso', ['package_id' => $package->id]);
            
            return $package->refresh()->load(['apartment.tower', 'apartment.block']);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar package', [
                'package_id' => $package->id,
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            return response()->json(['message' => 'Erro interno do servidor'], 500);
        }
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

        return $package->refresh()->load(['apartment.tower', 'apartment.block']);
    }
}
