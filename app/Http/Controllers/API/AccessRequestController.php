<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\Apartment;
use App\Models\ProtectiveMeasure;
use App\Models\Resident;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * @group Controle de Acessos
 *
 * Endpoints para gerenciar solicitações de acesso de visitantes.
 */
class AccessRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('tenant');
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
        $condominiumId = auth()->user()->condominium_id;

        $query = AccessRequest::condominium($condominiumId)
            ->with(['visitor', 'visitorVehicle', 'resident.user', 'apartment', 'parkingSpot']);

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
     * @bodyParam resident_id int ID do morador responsável. Example: 2
     * @bodyParam apartment_id int ID do apartamento. Example: 1
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
        $condominiumId = auth()->user()->condominium_id;

        $validated = $request->validate([
            'visitor_name' => 'required|string|max:255',
            'visitor_document' => 'required|string|max:255',
            'visitor_phone' => 'nullable|string|max:255',
            'visitor_photo_url' => 'nullable|url',
            'visitor_vehicle_plate' => 'nullable|string|max:20',
            'visitor_vehicle_model' => 'nullable|string|max:255',
            'resident_id' => ['required', Rule::exists('residents', 'id')->where(function ($query) use ($condominiumId) {
                return $query->where('condominium_id', $condominiumId);
            })],
            'apartment_id' => ['required', Rule::exists('apartments', 'id')->where(function ($query) use ($condominiumId) {
                return $query->where('condominium_id', $condominiumId);
            })],
            'visit_date' => 'required|date',
            'visit_time' => 'required|string',
            'duration_hours' => 'nullable|integer|min:1',
            'purpose' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
        ]);

        // Check for protective measures
        $protectiveMeasure = $this->_checkProtectiveMeasures(
            $condominiumId,
            $validated['visitor_document'],
            $validated['visitor_vehicle_plate'] ?? null
        );

        if ($protectiveMeasure) {
            return response()->json([
                'message' => 'Medida protetiva ativa encontrada para este visitante/veículo.',
                'protective_measure' => $protectiveMeasure
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Find or create Visitor
            $visitor = Visitor::firstOrCreate(
                ['document' => $validated['visitor_document'], 'condominium_id' => $condominiumId],
                ['name' => $validated['visitor_name'], 'phone' => $validated['visitor_phone'], 'photo_url' => $validated['visitor_photo_url'] ?? null]
            );

            $visitorVehicle = null;
            if (isset($validated['visitor_vehicle_plate'])) {
                $visitorVehicle = VisitorVehicle::firstOrCreate(
                    ['plate' => $validated['visitor_vehicle_plate'], 'condominium_id' => $condominiumId],
                    ['model' => $validated['visitor_vehicle_model'] ?? null]
                );
            }

            $accessRequest = AccessRequest::create([
                'condominium_id' => $condominiumId,
                'visitor_id' => $visitor->id,
                'visitor_vehicle_id' => $visitorVehicle ? $visitorVehicle->id : null,
                'resident_id' => $validated['resident_id'],
                'apartment_id' => $validated['apartment_id'],
                'visitor_name' => $validated['visitor_name'],
                'visitor_document' => $validated['visitor_document'],
                'visitor_phone' => $validated['visitor_phone'] ?? null,
                'visit_date' => $validated['visit_date'],
                'visit_time' => $validated['visit_time'],
                'duration_hours' => $validated['duration_hours'] ?? null,
                'purpose' => $validated['purpose'] ?? null,
                'observations' => $validated['observations'] ?? null,
                'status' => 'pending', // Initial status
                'request_timestamp' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Solicitação de acesso criada com sucesso!',
                'access_request' => $accessRequest->load(['visitor', 'visitorVehicle', 'resident.user', 'apartment'])
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
        $accessRequest->load(['visitor', 'visitorVehicle', 'resident.user', 'apartment', 'parkingSpot']);
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
        $condominiumId = auth()->user()->condominium_id;

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
                'access_request' => $accessRequest->load(['visitor', 'visitorVehicle', 'resident.user', 'apartment'])
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
        $condominiumId = auth()->user()->condominium_id;

        $accessRequests = AccessRequest::condominium($condominiumId)
            ->whereIn('status', ['pending', 'authorized', 'entry_recorded'])
            ->with(['visitor', 'visitorVehicle', 'resident.user', 'apartment', 'parkingSpot'])
            ->orderByDesc('request_timestamp')
            ->get();

        // Obtenha todos os IDs de visitantes únicos dos pedidos de acesso
        $visitorIds = $accessRequests->pluck('visitor_id')->unique()->filter()->values();

        if ($visitorIds->isNotEmpty()) {
            // Encontre todas as medidas protetivas ativas para esses visitantes em uma única consulta
            $protectiveMeasures = ProtectiveMeasure::where('is_active', true)
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

        return response()->json($accessRequests);
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
        $condominiumId = auth()->user()->condominium_id;
        $residentId = auth()->user()->resident->id; // Assuming authenticated user is a resident

        $accessRequests = AccessRequest::condominium($condominiumId)
            ->where('resident_id', $residentId)
            ->whereIn('status', ['pending', 'authorized'])
            ->with(['visitor', 'visitorVehicle', 'resident.user', 'apartment'])
            ->orderByDesc('request_timestamp')
            ->get();

        return response()->json($accessRequests);
    }

    /**
     * Responder a uma solicitação.
     *
     * @urlParam access_request int ID da solicitação.
     * @bodyParam response string required Resposta do morador (authorized ou unauthorized). Example: authorized
     * @bodyParam resident_observation string Observação opcional. Example: "Pode subir"
     *
     * @response { "message": "Resposta registrada com sucesso!" }
     */
    public function respondToRequest(Request $request, AccessRequest $accessRequest)
    {
        $validated = $request->validate([
            'response' => ['required', Rule::in(['authorized', 'unauthorized'])],
            'resident_observation' => 'nullable|string',
        ]);

        $accessRequest->update([
            'status' => $validated['response'],
            'resident_observation' => $validated['resident_observation'],
            'resident_response_timestamp' => now(),
        ]);

        return response()->json(['message' => 'Resposta registrada com sucesso!']);
    }

    /**
     * Registrar entrada do visitante.
     *
     * @urlParam access_request int ID da solicitação.
     * @bodyParam parking_spot_id int ID da vaga utilizada. Example: 5
     * @bodyParam gatehouse_entry_observation string Observação da portaria. Example: "Entrada confirmada"
     *
     * @response { "message": "Entrada registrada com sucesso!" }
     */
    public function recordEntry(Request $request, AccessRequest $accessRequest)
    {
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

        return response()->json(['message' => 'Entrada registrada com sucesso!']);
    }

    /**
     * Registrar saída do visitante.
     *
     * @urlParam access_request int ID da solicitação.
     * @bodyParam gatehouse_exit_observation string Observação da portaria. Example: "Saída registrada"
     *
     * @response { "message": "Saída registrada com sucesso!" }
     */
    public function recordExit(Request $request, AccessRequest $accessRequest)
    {
        $validated = $request->validate([
            'gatehouse_exit_observation' => 'nullable|string',
        ]);

        $accessRequest->update([
            'status' => 'exit_recorded',
            'gatehouse_observation' => $validated['gatehouse_exit_observation'] ?? null,
            'actual_exit_timestamp' => now(),
        ]);

        return response()->json(['message' => 'Saída registrada com sucesso!']);
    }

    /**
     * Cancelar uma solicitação.
     *
     * @urlParam access_request int ID da solicitação.
     * @bodyParam cancellation_observation string Motivo do cancelamento. Example: "Visitante não compareceu"
     *
     * @response { "message": "Solicitação cancelada com sucesso!" }
     */
    public function cancelRequest(Request $request, AccessRequest $accessRequest)
    {
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
        $condominiumId = auth()->user()->condominium_id;

        $query = AccessRequest::condominium($condominiumId)
            ->with(['visitor', 'visitorVehicle', 'resident.user', 'apartment', 'parkingSpot']);

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
        $condominiumId = auth()->user()->condominium_id;

        $request->validate([
            'visitor_document' => 'nullable|string',
            'vehicle_plate' => 'nullable|string',
        ]);

        $visitorDocument = $request->input('visitor_document');
        $vehiclePlate = $request->input('vehicle_plate');

        if (!$visitorDocument && !$vehiclePlate) {
            return response()->json(['message' => 'Pelo menos um documento de visitante ou placa de veículo deve ser fornecido.'], 422);
        }

        $protectiveMeasure = $this->_checkProtectiveMeasures($condominiumId, $visitorDocument, $vehiclePlate);

        if ($protectiveMeasure) {
            return response()->json([
                'has_protective_measure' => true,
                'measure' => $protectiveMeasure->load(['aggressorVisitor', 'protectedResident.user'])
            ]);
        }

        return response()->json(['has_protective_measure' => false]);
    }

    /**
     * Helper method to check for active protective measures.
     *
     * @param int $condominiumId
     * @param string|null $visitorDocument
     * @param string|null $vehiclePlate
     * @return ProtectiveMeasure|null
     */
    private function _checkProtectiveMeasures(?int $condominiumId, ?string $visitorDocument, ?string $vehiclePlate)
    {
        $query = ProtectiveMeasure::where('is_active', true)
            ->where(function ($q) use ($condominiumId) {
                $q->whereNull('condominium_id') // Global measure
                    ->orWhere('condominium_id', $condominiumId); // Condominium specific measure
            })
            ->where(function ($q) use ($visitorDocument, $vehiclePlate) {
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
}