<?php

namespace App\Http\Controllers\API;

use App\Events\Access\AccessRequestEntryWasRecorded;
use App\Events\Access\AccessRequestExitWasRecorded;
use App\Events\Access\AccessRequestWasCreated;
use App\Events\Access\AccessRequestWasResponded;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\Apartment;
use App\Models\ProtectiveMeasure;
use App\Models\Resident;
use App\Models\Visitor;
use App\Models\VisitorVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * @group Controle de Acessos
 *
 * Endpoints para gerenciar solicitações de acesso de visitantes.
 */
class AccessRequestController extends Controller
{
    use ResolvesTenantCondominium;

    public function __construct()
    {
        $this->middleware('auth:api')->except(['publicInvite']);
        $this->middleware('tenant')->except(['publicInvite']);
    }

    /**
     * Listar solicitações de acesso.
     *
     * Retorna uma lista filtrável de solicitações cadastradas.
     *
     * @queryParam status string Filtra por status. Example: authorized
     * @queryParam exclude_status string Exclui determinado status. Example: cancelled
     *
     * @response { "data": [ { "id": 1, "status": "pending", ... } ], "meta": { ... } }
     */
    public function index(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $query = AccessRequest::query()
            ->where('condominium_id', $condominiumId)
            ->with(['visitor', 'visitorVehicle', 'resident', 'apartment', 'parkingSpot']);

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('exclude_status') && $request->exclude_status !== '') {
            $query->where('status', '!=', $request->exclude_status);
        }

        $accessRequests = $query->orderByDesc('request_timestamp')->paginate($request->get('per_page', 15));

        return response()->json($accessRequests);
    }

    /**
     * Criar uma nova solicitação de acesso.
     *
     * @bodyParam visitor_name string required Nome do visitante. Example: João Silva
     * @bodyParam visitor_document string required Documento do visitante. Example: 123.456.789-00
     * @bodyParam visitor_phone string Telefone do visitante. Example: (11) 99999-9999
     * @bodyParam visitor_photo_url string URL da foto do visitante.
     * @bodyParam visitor_vehicle_plate string Placa do veículo do visitante. Example: ABC-1234
     * @bodyParam visitor_vehicle_model string Modelo do veículo do visitante. Example: Honda Civic
     * @bodyParam apartment_id int required ID da unidade de destino. Example: 1
     * @bodyParam resident_id int ID de morador vinculado para compatibilidade. Quando ausente, o sistema infere pela unidade. Example: 2
     * @bodyParam visit_date date Data da visita. Example: 2024-12-25
     * @bodyParam visit_time string Hora da visita. Example: 14:00
     * @bodyParam duration_hours int Duração da visita em horas. Example: 2
     * @bodyParam purpose string Propósito da visita. Example: Visita familiar
     * @bodyParam observations string Observações adicionais.
     *
     * @response { "message": "Solicitação de acesso criada com sucesso!" }
     * @response 422 { "message": "Medida protetiva ativa encontrada para este visitante/veículo." }
     */
    public function store(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'visitor_name' => 'required|string|max:255',
            'visitor_document' => 'required|string|max:255',
            'visitor_phone' => 'nullable|string|max:255',
            'visitor_observations' => 'nullable|string',
            'visitor_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'visitor_photo_url' => 'nullable|url',
            'visitor_id' => ['nullable', Rule::exists('visitors', 'id')->where(function ($query) use ($condominiumId) {
                return $query->where('condominium_id', $condominiumId);
            })],
            'resident_id' => ['nullable', Rule::exists('residents', 'id')->where(function ($query) use ($condominiumId) {
                return $query->where('condominium_id', $condominiumId);
            })],
            'apartment_id' => ['required', Rule::exists('apartments', 'id')->where(function ($query) use ($condominiumId) {
                return $query->where('condominium_id', $condominiumId);
            })],
            'visit_date' => 'nullable|date',
            'visit_time' => 'nullable|string',
            'duration_hours' => 'nullable|integer|min:1',
            'purpose' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
            'gatehouse_observation' => 'nullable|string',
            'has_vehicle' => 'nullable|boolean',
            'visitor_vehicle_id' => ['nullable', Rule::exists('visitor_vehicles', 'id')->where(function ($query) use ($condominiumId) {
                return $query->where('condominium_id', $condominiumId);
            })],
            'visitor_vehicle_plate' => 'nullable|string|max:20',
            'visitor_vehicle_model' => 'nullable|string|max:255',
            'vehicle_plate' => 'nullable|string|max:20',
            'vehicle_model' => 'nullable|string|max:255',
            'vehicle_color' => 'nullable|string|max:255',
            'vehicle.plate' => 'nullable|string|max:20',
            'vehicle.model' => 'nullable|string|max:255',
            'vehicle.color' => 'nullable|string|max:255',
        ]);

        $apartment = Apartment::where('condominium_id', $condominiumId)->find($validated['apartment_id']);

        if (! $apartment) {
            return response()->json([
                'message' => 'Unidade não encontrada para o condomínio atual.',
                'errors' => [
                    'apartment_id' => ['A unidade informada não pertence ao condomínio atual.'],
                ],
            ], 422);
        }

        $resident = $this->resolveResidentForApartment($apartment, $validated['resident_id'] ?? null);

        if (! $resident) {
            return response()->json([
                'message' => 'A unidade de destino não possui morador vinculado para receber a autorização.',
                'errors' => [
                    'apartment_id' => ['Vincule ao menos um morador à unidade antes de criar a solicitação.'],
                ],
            ], 422);
        }

        $visitAt = isset($validated['visit_date'])
            ? \Illuminate\Support\Carbon::parse($validated['visit_date'])
            : now();
        $visitTime = $validated['visit_time'] ?? $visitAt->format('H:i:s');

        $vehiclePlate = $validated['visitor_vehicle_plate']
            ?? $validated['vehicle_plate']
            ?? data_get($validated, 'vehicle.plate');
        $vehicleModel = $validated['visitor_vehicle_model']
            ?? $validated['vehicle_model']
            ?? data_get($validated, 'vehicle.model');
        $vehicleColor = $validated['vehicle_color'] ?? data_get($validated, 'vehicle.color');

        // Check for protective measures
        $protectiveMeasure = $this->_checkProtectiveMeasures(
            $condominiumId,
            $validated['visitor_document'],
            $vehiclePlate
        );

        if ($protectiveMeasure) {
            return response()->json([
                'message' => 'Medida protetiva ativa encontrada para este visitante/veículo.',
                'medida_protetiva_ativa' => true,
                'protective_measure' => $protectiveMeasure,
            ], 422);
        }

        DB::beginTransaction();
        try {
            $photoUrl = $validated['visitor_photo_url'] ?? null;

            if ($request->hasFile('visitor_photo')) {
                $path = $request->file('visitor_photo')->store('visitors_photos', 'public');
                $photoUrl = Storage::url($path);
            }

            // Find or create Visitor
            $visitor = isset($validated['visitor_id'])
                ? Visitor::where('condominium_id', $condominiumId)->findOrFail($validated['visitor_id'])
                : Visitor::firstOrCreate(
                    ['document' => $validated['visitor_document'], 'condominium_id' => $condominiumId],
                    [
                        'name' => $validated['visitor_name'],
                        'phone' => $validated['visitor_phone'] ?? null,
                        'photo_url' => $photoUrl,
                        'observations' => $validated['visitor_observations'] ?? null,
                    ]
                );

            $visitor->fill([
                'name' => $validated['visitor_name'],
                'phone' => $validated['visitor_phone'] ?? $visitor->phone,
                'photo_url' => $photoUrl ?? $visitor->photo_url,
                'observations' => $validated['visitor_observations'] ?? $visitor->observations,
            ])->save();

            $visitorVehicle = null;
            if (! empty($validated['visitor_vehicle_id'])) {
                $visitorVehicle = VisitorVehicle::where('condominium_id', $condominiumId)
                    ->where('visitor_id', $visitor->id)
                    ->findOrFail($validated['visitor_vehicle_id']);
            } elseif (! empty($vehiclePlate)) {
                $visitorVehicle = VisitorVehicle::firstOrCreate(
                    [
                        'license_plate' => mb_strtoupper($vehiclePlate),
                        'condominium_id' => $condominiumId,
                        'visitor_id' => $visitor->id,
                    ],
                    [
                        'brand' => $vehicleModel ?: 'Não informado',
                        'model' => $vehicleModel ?: 'Não informado',
                        'color' => $vehicleColor ?: 'Não informado',
                        'plate' => mb_strtoupper($vehiclePlate),
                    ]
                );

                $visitorVehicle->fill([
                    'visitor_id' => $visitor->id,
                    'model' => $vehicleModel ?: $visitorVehicle->model,
                    'color' => $vehicleColor ?: $visitorVehicle->color,
                    'plate' => mb_strtoupper($vehiclePlate),
                ])->save();
            }

            $accessRequest = AccessRequest::create([
                'condominium_id' => $condominiumId,
                'visitor_id' => $visitor->id,
                'visitor_vehicle_id' => $visitorVehicle ? $visitorVehicle->id : null,
                'resident_id' => $resident->id,
                'apartment_id' => $apartment->id,
                'visitor_name' => $validated['visitor_name'],
                'visitor_document' => $validated['visitor_document'],
                'visitor_phone' => $validated['visitor_phone'] ?? null,
                'visit_date' => $visitAt,
                'visit_time' => $visitTime,
                'duration_hours' => $validated['duration_hours'] ?? null,
                'purpose' => $validated['purpose'] ?? null,
                'observations' => $validated['observations'] ?? $validated['visitor_observations'] ?? null,
                'gatehouse_observation' => $validated['gatehouse_observation'] ?? null,
                'status' => 'pending', // Initial status
                'request_timestamp' => now(),
            ]);

            DB::commit();

            event(new AccessRequestWasCreated(
                condominiumId: $condominiumId,
                actorId: auth()->id(),
                accessRequestId: $accessRequest->id,
            ));

            return response()->json([
                'message' => 'Solicitação de acesso criada com sucesso!',
                'access_request' => $accessRequest->load(['visitor', 'visitorVehicle', 'resident', 'apartment']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Erro ao criar solicitação de acesso', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Exibir detalhes de uma solicitação de acesso.
     *
     * @urlParam access_request int ID da solicitação de acesso.
     *
     * @response { "id": 1, "status": "pending", ... }
     */
    public function show(AccessRequest $accessRequest)
    {
        $this->authorizeTenantAccess($accessRequest);
        $accessRequest->load(['visitor', 'visitorVehicle', 'resident', 'apartment', 'parkingSpot']);

        return response()->json($accessRequest);
    }

    /**
     * Atualizar uma solicitação de acesso.
     *
     * @urlParam access_request int ID da solicitação de acesso.
     *
     * @response { "message": "Solicitação de acesso atualizada com sucesso!" }
     */
    public function update(Request $request, AccessRequest $accessRequest)
    {
        $condominiumId = $this->tenantCondominiumId($request);
        $this->authorizeTenantAccess($accessRequest, $condominiumId);

        $validated = $request->validate([
            'visitor_name' => 'sometimes|required|string|max:255',
            'visitor_document' => 'sometimes|required|string|max:255',
            'visitor_phone' => 'nullable|string|max:255',
            'visitor_photo_url' => 'nullable|url',
            'visitor_vehicle_plate' => 'nullable|string|max:20',
            'visitor_vehicle_model' => 'nullable|string|max:255',
            'resident_id' => ['sometimes', 'required', Rule::exists('residents', 'id')->where(function ($query) use ($condominiumId) {
                return $query->where('condominium_id', $condominiumId);
            })],
            'apartment_id' => ['sometimes', 'required', Rule::exists('apartments', 'id')->where(function ($query) use ($condominiumId) {
                return $query->where('condominium_id', $condominiumId);
            })],
            'visit_date' => 'sometimes|required|date',
            'visit_time' => 'sometimes|required|string',
            'duration_hours' => 'nullable|integer|min:1',
            'purpose' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
            'status' => 'sometimes|required|string',
            'authorized_by' => 'nullable|integer|exists:users,id',
            'authorized_at' => 'nullable|date',
            'rejection_reason' => 'nullable|string',
            'entry_time' => 'nullable|date',
            'exit_time' => 'nullable|date',
            'resident_observation' => 'nullable|string',
            'gatehouse_observation' => 'nullable|string',
            'resident_response_timestamp' => 'nullable|date',
            'actual_entry_timestamp' => 'nullable|date',
            'actual_exit_timestamp' => 'nullable|date',
            'parking_spot_id' => 'nullable|exists:parking_spots,id',
        ]);

        DB::beginTransaction();
        try {
            unset($validated['condominium_id']);
            $accessRequest->update($validated);

            // Update Visitor if necessary
            if (isset($validated['visitor_document'])) {
                $visitor = Visitor::firstOrCreate(
                    ['document' => $validated['visitor_document'], 'condominium_id' => $condominiumId],
                    ['name' => $validated['visitor_name'] ?? $accessRequest->visitor_name, 'phone' => $validated['visitor_phone'] ?? $accessRequest->visitor_phone, 'photo_url' => $validated['visitor_photo_url'] ?? $accessRequest->visitor_photo_url]
                );
                $accessRequest->visitor_id = $visitor->id;
                $accessRequest->save();
            }

            // Update VisitorVehicle if necessary
            if (isset($validated['visitor_vehicle_plate'])) {
                $visitorVehicle = VisitorVehicle::firstOrCreate(
                    ['plate' => $validated['visitor_vehicle_plate'], 'condominium_id' => $condominiumId],
                    ['model' => $validated['visitor_vehicle_model'] ?? null]
                );
                $accessRequest->visitor_vehicle_id = $visitorVehicle->id;
                $accessRequest->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Solicitação de acesso atualizada com sucesso!',
                'access_request' => $accessRequest->load(['visitor', 'visitorVehicle', 'resident', 'apartment']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Erro ao atualizar solicitação de acesso', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Excluir uma solicitação de acesso.
     *
     * @urlParam access_request int ID da solicitação de acesso.
     *
     * @response 204 null
     */
    public function destroy(AccessRequest $accessRequest)
    {
        $this->authorizeTenantAccess($accessRequest);
        $accessRequest->delete();

        return response()->json(null, 204);
    }

    /**
     * Listagem para portaria.
     *
     * Retorna todas as solicitações do condomínio para uso na guarita.
     *
     * @response { "data": [ { "id": 1, "status": "pending", ... } ] }
     */
    public function listForGatehouse(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $accessRequests = AccessRequest::query()
            ->where('condominium_id', $condominiumId)
            ->whereIn('status', ['pending', 'authorized', 'entry_recorded'])
            ->with(['visitor', 'visitorVehicle', 'resident', 'apartment', 'parkingSpot'])
            ->orderByDesc('request_timestamp')
            ->get();

        // Obtenha todos os IDs de visitantes únicos dos pedidos de acesso
        $visitorIds = $accessRequests->pluck('visitor_id')->unique()->filter()->values();

        if ($visitorIds->isNotEmpty()) {
            // Encontre todas as medidas protetivas ativas para esses visitantes em uma única consulta
            $protectiveMeasures = ProtectiveMeasure::withoutGlobalScopes()
                ->where('is_active', true)
                ->where(function ($q) use ($condominiumId) {
                    $q->whereNull('condominium_id')
                        ->orWhere('condominium_id', $condominiumId);
                })
                ->whereIn('aggressor_visitor_id', $visitorIds)
                ->with('protectedResident:id,name') // Carregue as informações do residente protegido
                ->get()
                ->keyBy('aggressor_visitor_id'); // Mapeie por aggressor_visitor_id para fácil consulta

            // Anexe as informações da medida protetiva a cada pedido de acesso
            $accessRequests->each(function ($request) use ($protectiveMeasures) {
                if (isset($request->visitor_id) && $protectiveMeasures->has($request->visitor_id)) {
                    $request->protective_measure = $protectiveMeasures[$request->visitor_id];
                } else {
                    $request->protective_measure = null;
                }
            });
        } else {
            // Garanta que o atributo protective_measure seja definido mesmo se não houver visitantes
            $accessRequests->each(function ($request) {
                $request->protective_measure = null;
            });
        }

        return response()->json(['data' => $accessRequests]);
    }

    /**
     * Solicitações do morador autenticado.
     *
     * Lista apenas as solicitações pendentes do usuário.
     *
     * @response { "data": [ { "id": 1, "status": "pending", ... } ] }
     */
    public function listMyRequestsAsResident(Request $request)
    {
        $user = auth()->user();
        $condominiumId = $this->tenantCondominiumId($request);
        $resident = Resident::where('condominium_id', $condominiumId)
            ->where('email', $user->email)
            ->first();

        if (! $resident) {
            return response()->json(['data' => []]);
        }

        $accessRequests = AccessRequest::query()
            ->where('condominium_id', $condominiumId)
            ->where(function ($query) use ($resident) {
                $apartmentIds = collect([$resident->apartment_id])
                    ->merge($resident->apartments()->pluck('apartments.id'))
                    ->merge($resident->ownedApartments()->pluck('apartments.id'))
                    ->merge($resident->rentedApartments()->pluck('apartments.id'))
                    ->filter()
                    ->unique()
                    ->values();

                $query->where('resident_id', $resident->id);

                if ($apartmentIds->isNotEmpty()) {
                    $query->orWhereIn('apartment_id', $apartmentIds);
                }
            })
            ->whereIn('status', ['pending', 'authorized'])
            ->with(['visitor', 'visitorVehicle', 'resident', 'apartment'])
            ->orderByDesc('request_timestamp')
            ->get();

        return response()->json(['data' => $accessRequests]);
    }

    /**
     * Responder a uma solicitação.
     *
     * @urlParam access_request int ID da solicitação.
     *
     * @bodyParam response string required Resposta do morador (authorized ou unauthorized). Example: authorized
     * @bodyParam resident_observation string Observação opcional. Example: "Pode subir"
     *
     * @response { "message": "Resposta registrada com sucesso!" }
     */
    public function respondToRequest(Request $request, AccessRequest $accessRequest)
    {
        $this->authorizeTenantAccess($accessRequest);
        $validated = $request->validate([
            'response' => ['required', Rule::in([
                'authorized',
                'unauthorized',
                'inform_not_home',
                'awaiting_at_gatehouse',
                'await_at_gatehouse',
            ])],
            'resident_observation' => 'nullable|string',
        ]);

        $status = $validated['response'] === 'await_at_gatehouse'
            ? 'awaiting_at_gatehouse'
            : $validated['response'];

        $accessRequest->update([
            'status' => $status,
            'resident_observation' => $validated['resident_observation'],
            'resident_response_timestamp' => now(),
        ]);

        if (in_array($status, ['authorized', 'unauthorized'], true)) {
            event(new AccessRequestWasResponded(
                condominiumId: $accessRequest->condominium_id,
                actorId: auth()->id(),
                accessRequestId: $accessRequest->id,
                status: $status,
            ));
        }

        return response()->json(['message' => 'Resposta registrada com sucesso!']);
    }

    /**
     * Registrar entrada do visitante.
     *
     * @urlParam access_request int ID da solicitação.
     *
     * @bodyParam parking_spot_id int ID da vaga utilizada. Example: 5
     * @bodyParam gatehouse_entry_observation string Observação da portaria. Example: "Entrada confirmada"
     *
     * @response { "message": "Entrada registrada com sucesso!" }
     */
    public function recordEntry(Request $request, AccessRequest $accessRequest)
    {
        $this->authorizeTenantAccess($accessRequest);
        $validated = $request->validate([
            'parking_spot_id' => 'nullable|exists:parking_spots,id',
            'gatehouse_entry_observation' => 'nullable|string',
        ]);

        $accessRequest->update([
            'status' => 'entry_recorded',
            'parking_spot_id' => $validated['parking_spot_id'] ?? null,
            'gatehouse_observation' => $validated['gatehouse_entry_observation'] ?? null,
            'actual_entry_timestamp' => now(),
        ]);

        event(new AccessRequestEntryWasRecorded(
            condominiumId: $accessRequest->condominium_id,
            actorId: auth()->id(),
            accessRequestId: $accessRequest->id,
        ));

        return response()->json(['message' => 'Entrada registrada com sucesso!']);
    }

    /**
     * Registrar saída do visitante.
     *
     * @urlParam access_request int ID da solicitação.
     *
     * @bodyParam gatehouse_exit_observation string Observação da portaria. Example: "Saída registrada"
     *
     * @response { "message": "Saída registrada com sucesso!" }
     */
    public function recordExit(Request $request, AccessRequest $accessRequest)
    {
        $this->authorizeTenantAccess($accessRequest);
        $validated = $request->validate([
            'gatehouse_exit_observation' => 'nullable|string',
        ]);

        $accessRequest->update([
            'status' => 'exit_recorded',
            'gatehouse_observation' => $validated['gatehouse_exit_observation'] ?? null,
            'actual_exit_timestamp' => now(),
        ]);

        event(new AccessRequestExitWasRecorded(
            condominiumId: $accessRequest->condominium_id,
            actorId: auth()->id(),
            accessRequestId: $accessRequest->id,
        ));

        return response()->json(['message' => 'Saída registrada com sucesso!']);
    }

    /**
     * Cancelar uma solicitação.
     *
     * @urlParam access_request int ID da solicitação.
     *
     * @bodyParam cancellation_observation string Motivo do cancelamento. Example: "Visitante não compareceu"
     *
     * @response { "message": "Solicitação cancelada com sucesso!" }
     */
    public function cancelRequest(Request $request, AccessRequest $accessRequest)
    {
        $this->authorizeTenantAccess($accessRequest);
        $validated = $request->validate([
            'cancellation_observation' => 'nullable|string',
        ]);

        $accessRequest->update([
            'status' => 'cancelled',
            'rejection_reason' => $validated['cancellation_observation'] ?? null,
        ]);

        return response()->json(['message' => 'Solicitação cancelada com sucesso!']);
    }

    /**
     * Histórico de solicitações.
     *
     * Permite filtrar por status e consultar de forma paginada.
     *
     * @queryParam status string Filtrar por status específico. Example: authorized
     * @queryParam page int Página desejada. Example: 1
     * @queryParam per_page int Itens por página. Example: 15
     *
     * @response { "data": [ { "id": 1, "status": "authorized", ... } ], "meta": { ... } }
     */
    public function history(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $query = AccessRequest::query()
            ->where('condominium_id', $condominiumId)
            ->with(['visitor', 'visitorVehicle', 'resident', 'apartment', 'parkingSpot']);

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $accessRequests = $query->orderByDesc('request_timestamp')->paginate($request->get('per_page', 15));

        return response()->json($accessRequests);
    }

    /**
     * Verifica se há medida protetiva ativa para um visitante ou veículo.
     *
     * @queryParam visitor_document string Documento do visitante. Example: 123.456.789-00
     * @queryParam vehicle_plate string Placa do veículo. Example: ABC-1234
     *
     * @response { "has_protective_measure": true, "measure": { ... } }
     * @response { "has_protective_measure": false }
     */
    public function checkProtectiveMeasure(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $request->validate([
            'visitor_document' => 'nullable|string',
            'vehicle_plate' => 'nullable|string',
        ]);

        $visitorDocument = $request->input('visitor_document');
        $vehiclePlate = $request->input('vehicle_plate');

        if (! $visitorDocument && ! $vehiclePlate) {
            return response()->json(['message' => 'Pelo menos um documento de visitante ou placa de veículo deve ser fornecido.'], 422);
        }

        $protectiveMeasure = $this->_checkProtectiveMeasures($condominiumId, $visitorDocument, $vehiclePlate);

        if ($protectiveMeasure) {
            return response()->json([
                'has_protective_measure' => true,
                'measure' => $protectiveMeasure->load(['aggressorVisitor', 'protectedResident']),
            ]);
        }

        return response()->json(['has_protective_measure' => false]);
    }

    /**
     * Helper method to check for active protective measures.
     *
     * @return ProtectiveMeasure|null
     */
    private function _checkProtectiveMeasures(?int $condominiumId, ?string $visitorDocument, ?string $vehiclePlate)
    {
        $query = ProtectiveMeasure::withoutGlobalScopes()
            ->where('is_active', true)
            ->where(function ($q) use ($condominiumId) {
                $q->whereNull('condominium_id')
                    ->orWhere('condominium_id', $condominiumId);
            })
            ->where(function ($q) use ($visitorDocument) {
                if ($visitorDocument) {
                    $q->orWhereHas('aggressorVisitor', function ($sq) use ($visitorDocument) {
                        $sq->where('document', $visitorDocument);
                    });
                }
                // Note: Protective measures are currently only linked to visitors, not directly to vehicles.
                // If vehicle-specific protective measures are needed, the ProtectiveMeasure model and migration
                // would need to be updated to include a vehicle_id.
            });

        return $query->first();
    }

    private function authorizeTenantAccess(AccessRequest $accessRequest, ?int $condominiumId = null): void
    {
        $tenantId = $condominiumId ?? $this->tenantCondominiumId(request());
        abort_if($accessRequest->condominium_id !== $tenantId, 404);
    }

    private function resolveResidentForApartment(Apartment $apartment, ?int $residentId): ?Resident
    {
        if ($residentId) {
            return Resident::where('condominium_id', $apartment->condominium_id)->find($residentId);
        }

        if ($apartment->owner_id) {
            return Resident::where('condominium_id', $apartment->condominium_id)->find($apartment->owner_id);
        }

        if ($apartment->tenant_id) {
            return Resident::where('condominium_id', $apartment->condominium_id)->find($apartment->tenant_id);
        }

        $pivotResident = $apartment->residents()
            ->wherePivot('active', true)
            ->first();

        if ($pivotResident) {
            return $pivotResident;
        }

        return Resident::where('condominium_id', $apartment->condominium_id)
            ->where('apartment_id', $apartment->id)
            ->first();
    }

    /**
     * @group Controle de Acessos
     *
     * Consulta pública de convite digital de visitante via token para portaria ou convidado (QR Code).
     *
     * @unauthenticated
     *
     * @urlParam token string required Token seguro do convite. Example: 4fa6791e847c28...
     */
    public function publicInvite(string $token)
    {
        $accessRequest = AccessRequest::withoutGlobalScopes()
            ->where('invite_token', $token)
            ->with([
                'condominium:id,name',
                'apartment.tower',
                'apartment.block',
                'resident:id,name',
                'visitor:id,name',
                'visitorVehicle',
            ])
            ->firstOrFail();

        return response()->json([
            'id' => $accessRequest->id,
            'invite_token' => $accessRequest->invite_token,
            'status' => $accessRequest->status,
            'visitor_name' => $accessRequest->visitor_name,
            'visitor_document' => $accessRequest->visitor_document,
            'visit_date' => $accessRequest->visit_date,
            'visit_time' => $accessRequest->visit_time,
            'purpose' => $accessRequest->purpose,
            'condominium' => [
                'id' => $accessRequest->condominium?->id,
                'name' => $accessRequest->condominium?->name,
            ],
            'apartment' => [
                'number' => $accessRequest->apartment?->number,
                'tower' => $accessRequest->apartment?->tower?->name,
                'block' => $accessRequest->apartment?->block?->name,
            ],
            'resident_name' => $accessRequest->resident?->name,
            'qr_payload' => json_encode([
                'type' => 'condo_invite',
                'token' => $accessRequest->invite_token,
                'id' => $accessRequest->id,
            ]),
        ]);
    }

    /**
     * @group Controle de Acessos
     *
     * @authenticated
     * Busca rápida da autorização de acesso pelo token lido via QR Code na portaria.
     *
     * @urlParam token string required Token seguro do convite. Example: 4fa6791e847c28...
     */
    public function showByToken(Request $request, string $token)
    {
        $condoId = $this->tenantCondominiumId($request);
        $accessRequest = AccessRequest::where('condominium_id', $condoId)
            ->where('invite_token', $token)
            ->with(['visitor', 'visitorVehicle', 'resident', 'apartment.tower', 'apartment.block', 'parkingSpot'])
            ->firstOrFail();

        return response()->json($accessRequest);
    }
}
