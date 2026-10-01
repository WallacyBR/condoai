<?php

namespace App\Http\Controllers\API;

use App\Enums\ResidentClassification;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Services\ResidentUserLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ResidentController extends Controller
{
    use ResolvesTenantCondominium;

    public function __construct(
        private readonly ResidentUserLinkService $residentUserLinkService
    ) {
    }

    /**
     * Converte strings booleanas e números para booleanos reais
     */
    private function convertBooleanFields(array $data): array
    {
        $booleanFields = ['is_porter', 'is_janitor', 'is_cleaning', 'active'];
        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                if (is_string($data[$field])) {
                    $data[$field] = filter_var($data[$field], FILTER_VALIDATE_BOOLEAN);
                } elseif (is_numeric($data[$field])) {
                    $data[$field] = (bool) $data[$field];
                }
            }
        }

        return $data;
    }

    public function index(Request $request)
    {
        $query = Resident::with(['condominium', 'apartment']);

        if ($request->has('apartment_id')) {
            $query->where(function ($q) use ($request) {
                $apartmentId = $request->apartment_id;
                $q->where('apartment_id', $apartmentId)
                    ->orWhereHas('apartments', function ($subQuery) use ($apartmentId) {
                        $subQuery->where('apartments.id', $apartmentId);
                    })
                    ->orWhereHas('ownedApartments', function ($subQuery) use ($apartmentId) {
                        $subQuery->where('apartments.id', $apartmentId);
                    })
                    ->orWhereHas('rentedApartments', function ($subQuery) use ($apartmentId) {
                        $subQuery->where('apartments.id', $apartmentId);
                    });
            });
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        // Determinar o condomínio automaticamente
        $condominiumId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rg' => 'nullable|string|max:30',
            'cpf' => 'required|string|max:20',
            'cell_phone' => 'nullable|string|max:20',
            'commercial_phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'required|email|unique:residents,email',
            'person_type' => ['required', Rule::in(['fisica', 'juridica'])],
            'photo' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_porter' => 'boolean',
            'is_janitor' => 'boolean',
            'is_cleaning' => 'boolean',
            'apartment_id' => [
                'nullable',
                Rule::exists('apartments', 'id')->where(function ($query) use ($condominiumId) {
                    return $query->where('condominium_id', $condominiumId);
                }),
            ],
            'billing_address_type' => 'required|in:condominium,custom',
            'cep' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'classification' => ['required', Rule::in(['proprietario', 'morador', 'inquilino'])],
            'active' => 'boolean',
            'owned_apartment_ids' => 'nullable|array',
            'owned_apartment_ids.*' => [
                'integer',
                Rule::exists('apartments', 'id')->where(function ($query) use ($condominiumId) {
                    return $query->where('condominium_id', $condominiumId);
                }),
            ],
        ]);

        // Converter strings booleanas para booleanos se necessário
        $validated = $this->convertBooleanFields($validated);
        unset($validated['condominium_id']);
        $validated['condominium_id'] = $condominiumId;

        $resident = Resident::create($validated);

        if ($resident->classification == ResidentClassification::PROPRIETARIO && ! empty($validated['owned_apartment_ids'])) {
            \App\Models\Apartment::whereIn('id', $validated['owned_apartment_ids'])
                ->update(['owner_id' => $resident->id]);
        }

        return response()->json($resident->load('ownedApartments'), 201);
    }

    public function show(Resident $resident)
    {
        return $resident->load(['condominium', 'ownedApartments'])->append('raw_classification');
    }

    public function update(Request $request, Resident $resident)
    {
        \Log::info('=== INÍCIO UPDATE RESIDENT ===');
        \Log::info('Resident ID:', ['id' => $resident->id]);
        \Log::info('Request method:', ['method' => $request->method()]);
        \Log::info('Request headers:', $request->headers->all());
        \Log::info('Request all data:', $request->all());
        \Log::info('Request input owned_apartment_ids:', ['data' => $request->input('owned_apartment_ids')]);
        \Log::info('Request input classification:', ['data' => $request->input('classification')]);
        \Log::info('Request input condominium_id:', ['data' => $request->input('condominium_id')]);

        // Debug temporário para identificar problemas
        \Log::info('Resident update request data:', [
            'resident_id' => $resident->id,
            'request_data' => $request->all(),
            'owned_apartment_ids' => $request->input('owned_apartment_ids'),
            'classification' => $request->input('classification'),
        ]);

        try {
            \Log::info('Iniciando validação...');

            // Determinar o condomínio automaticamente
            $condominiumId = $this->tenantCondominiumId($request);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'rg' => 'nullable|string|max:30',
                'cpf' => 'sometimes|string|max:20',
                'cell_phone' => 'nullable|string|max:20',
                'commercial_phone' => 'nullable|string|max:20',
                'whatsapp' => 'nullable|string|max:20',
                'email' => ['sometimes', 'email', Rule::unique('residents')->ignore($resident->id)],
                'person_type' => ['sometimes', Rule::in(['fisica', 'juridica'])],
                'photo' => 'nullable|string',
                'notes' => 'nullable|string',
                'is_porter' => 'sometimes|boolean',
                'is_janitor' => 'sometimes|boolean',
                'is_cleaning' => 'sometimes|boolean',
                'apartment_id' => [
                    'nullable',
                    Rule::exists('apartments', 'id')->where(function ($query) use ($condominiumId) {
                        return $query->where('condominium_id', $condominiumId);
                    }),
                ],
                'billing_address_type' => 'sometimes|in:condominium,custom',
                'cep' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'neighborhood' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:2',
                'classification' => ['sometimes', Rule::in(['proprietario', 'morador', 'inquilino'])],
                'active' => 'sometimes|boolean',
                'owned_apartment_ids' => 'nullable|array',
                'owned_apartment_ids.*' => [
                    'integer',
                    Rule::exists('apartments', 'id')->where(function ($query) use ($condominiumId) {
                        return $query->where('condominium_id', $condominiumId);
                    }),
                ],
            ]);

            \Log::info('Validação passou com sucesso!');
            \Log::info('Dados validados:', $validated);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('=== ERRO DE VALIDAÇÃO ===');
            \Log::error('Mensagem de erro:', ['message' => $e->getMessage()]);
            \Log::error('Erros de validação:', $e->errors());
            \Log::error('Dados que falharam na validação:', $request->all());
            throw $e;
        } catch (\Exception $e) {
            \Log::error('=== ERRO GERAL ===');
            \Log::error('Tipo de erro:', ['type' => get_class($e)]);
            \Log::error('Mensagem:', ['message' => $e->getMessage()]);
            throw $e;
        }

        // Converter strings booleanas para booleanos se necessário
        $validated = $this->convertBooleanFields($validated);
        unset($validated['condominium_id']);
        $validated['condominium_id'] = $condominiumId;

        \Log::info('Dados finais para atualização:', $validated);

        $resident->update($validated);

        // Atualizar relacionamentos de apartamentos possuídos
        if (isset($validated['owned_apartment_ids'])) {
            // Remover todas as relações existentes
            $resident->ownedApartments()->update(['owner_id' => null]);

            // Adicionar as novas relações
            if (! empty($validated['owned_apartment_ids'])) {
                \App\Models\Apartment::whereIn('id', $validated['owned_apartment_ids'])
                    ->update(['owner_id' => $resident->id]);
            }
        }

        \Log::info('=== FIM UPDATE RESIDENT ===');

        return $resident->load(['condominium', 'ownedApartments'])->append('raw_classification');
    }

    public function destroy(Resident $resident)
    {
        $resident->delete();

        return response()->json(null, 204);
    }

    /**
     * Vincula ou desvincula um usuário autenticável ao registro de morador (ADR-002 Q1).
     *
     * @authenticated
     */
    public function linkUser(Request $request, Resident $resident)
    {
        Gate::authorize('linkUser', $resident);

        $condominiumId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ]);

        try {
            $updated = $this->residentUserLinkService->link(
                $resident,
                $validated['user_id'] ?? null,
                $condominiumId
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($updated->load('user'));
    }
}
