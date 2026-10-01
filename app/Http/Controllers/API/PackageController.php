<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        $condominiumId = $this->currentCondominiumId($request);

        $validated = $request->validate([
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')->where(fn ($query) => $query->where('condominium_id', $condominiumId)),
            ],
            'recipient_name' => 'sometimes|nullable|string|max:255',
            'recipient_apartment' => 'sometimes|nullable|string|max:255',
            'recipient_phone' => 'sometimes|nullable|string|max:255',
            'sender' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'photo' => 'nullable|string',
            'pickup_location' => 'required|string|max:255',
            'observation' => 'nullable|string',
        ]);

        $apartment = Apartment::with(['owner', 'tenant'])->findOrFail($validated['apartment_id']);

        $data = $validated;
        $data['user_id'] = $request->user()->id;
        $data['condominium_id'] = $apartment->condominium_id;
        $data['recipient_name'] = $validated['recipient_name']
            ?? $apartment->owner?->name
            ?? $apartment->tenant?->name
            ?? 'Destinatário não informado';
        $data['recipient_apartment'] = $validated['recipient_apartment'] ?? $apartment->number;
        $data['sender'] = $validated['sender'] ?? 'Remetente não informado';

        if ($request->photo) {
            try {
                $image = $request->photo;
                // Remove o cabeçalho do base64
                [$type, $image] = explode(';', $image);
                [, $image] = explode(',', $image);
                $imageData = base64_decode($image);

                // Gera um nome de arquivo único
                $extension = str_replace('data:image/', '', $type);
                $filename = 'packages/'.Str::uuid().'.'.$extension;

                // Salva a imagem no armazenamento público
                Storage::disk('public')->put($filename, $imageData);

                // Salva o caminho público no banco de dados
                $data['photo'] = Storage::url($filename);

            } catch (\Exception $e) {
                \Log::error('Erro ao processar a imagem da correspondência: '.$e->getMessage());

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

    public function show(Package $package)
    {
        return $package->load(['apartment.tower', 'apartment.block']);
    }

    public function update(Request $request, Package $package)
    {
        $condominiumId = $this->currentCondominiumId($request);

        $validated = $request->validate([
            'apartment_id' => [
                'sometimes',
                'integer',
                Rule::exists('apartments', 'id')->where(fn ($query) => $query->where('condominium_id', $condominiumId)),
            ],
            'recipient_name' => 'sometimes|nullable|string|max:255',
            'recipient_apartment' => 'sometimes|nullable|string|max:255',
            'recipient_phone' => 'sometimes|nullable|string|max:255',
            'sender' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'photo' => 'sometimes|nullable|string',
            'pickup_location' => 'sometimes|string|max:255',
            'observation' => 'sometimes|nullable|string',
        ]);

        if (isset($validated['apartment_id'])) {
            $apartment = Apartment::findOrFail($validated['apartment_id']);
            $validated['condominium_id'] = $apartment->condominium_id;
            $validated['recipient_apartment'] = $validated['recipient_apartment'] ?? $apartment->number;
        }

        try {
            $package->update($validated);

            return $package->refresh()->load(['apartment.tower', 'apartment.block']);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar encomenda.', [
                'package_id' => $package->id,
                'error' => $e->getMessage(),
                'data' => $validated,
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

        if ($package->retrieved_at) {
            return response()->json(['message' => 'Encomenda já retirada'], 422);
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

    private function currentCondominiumId(Request $request): int
    {
        if (app()->bound('currentCondominium')) {
            return app('currentCondominium')->id;
        }

        if ($request->attributes->has('condominium')) {
            return $request->attributes->get('condominium')->id;
        }

        return (int) $request->user()->condominium_id;
    }
}
