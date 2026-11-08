<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * @group Manutenção - Equipamentos
 *
 * APIs para gerenciar equipamentos de manutenção.
 */
class MaintenanceEquipmentController extends Controller
{
    /**
     * Listar todos os equipamentos de manutenção.
     *
     * Retorna uma lista paginada dos equipamentos de manutenção do condomínio autenticado.
     * Pode ser filtrado por `maintenance_area_id`.
     *
     * @authenticated
     *
     * @queryParam page int A página a ser retornada. Example: 1
     * @queryParam per_page int Número de itens por página. Example: 15
     * @queryParam maintenance_area_id int ID da área de manutenção para filtrar equipamentos. Example: 1
     *
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenanceequipments.index.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     */
    public function index(Request $request)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (! $condominiumId && ! $request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        $perPage = $request->input('per_page', 15);
        $query = MaintenanceEquipment::where('condominium_id', $currentCondominiumId)
            ->with(['maintenanceArea:id,name', 'qrAccessUsers:id,name']); // Otimizar carregamento da área e usuários

        if ($request->has('maintenance_area_id')) {
            $query->where('maintenance_area_id', $request->maintenance_area_id);
        }

        $equipments = $query->orderBy('name', 'asc')->paginate($perPage);

        return response()->json($equipments);
    }

    /**
     * Criar um novo equipamento de manutenção.
     *
     * @authenticated
     *
     * @bodyParam name string required Nome do equipamento. Example: "Bomba Piscina Bloco A"
     * @bodyParam maintenance_area_id int required ID da área de manutenção à qual o equipamento pertence. Example: 1
     * @bodyParam description string Descrição do equipamento. Example: "Bomba responsável pela filtragem da piscina do Bloco A."
     * @bodyParam location_details string Detalhes da localização do equipamento. Example: "Casa de máquinas, subsolo."
     * @bodyParam qr_code_access_level string Nível de acesso ao QR Code. Valores: `public`, `internal_users`, `specific_users`. Default: `internal_users`. Example: "internal_users"
     *
     * @responseFile status=201 scenario="Criado com sucesso" storage/responses/maintenanceequipment.store.json
     * @responseFile status=400 scenario="Dados inválidos" storage/responses/validation.error.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     */
    public function store(Request $request)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (! $condominiumId && ! $request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'maintenance_area_id' => [
                'required',
                'integer',
                Rule::exists('maintenance_areas', 'id')->where(function ($query) use ($currentCondominiumId) {
                    $query->where('condominium_id', $currentCondominiumId);
                }),
            ],
            'description' => 'nullable|string',
            'location_details' => 'nullable|string|max:255',
            'qr_code_access_level' => ['sometimes', 'string', Rule::in(['public', 'internal_users', 'specific_users'])],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $equipment = MaintenanceEquipment::create([
            'name' => $request->name,
            'maintenance_area_id' => $request->maintenance_area_id,
            'description' => $request->description,
            'location_details' => $request->location_details,
            'qr_code_access_level' => $request->input('qr_code_access_level', 'internal_users'),
            'condominium_id' => $currentCondominiumId,
            'qr_code_hash' => Str::random(32), // Gerar um hash aleatório para o QR Code
        ]);

        return response()->json($equipment, 201);
    }

    /**
     * Exibir um equipamento de manutenção específico.
     *
     * @authenticated
     *
     * @urlParam equipment int required O ID do equipamento. Example: 1
     *
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenanceequipment.show.json
     * @responseFile status=404 scenario="Não encontrado" storage/responses/notfound.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     */
    public function show(Request $request, MaintenanceEquipment $equipment)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (! $condominiumId && ! $request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($equipment->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a este equipamento.'], 403);
        }

        $equipment->load(['maintenanceArea:id,name', 'qrAccessUsers:id,name']); // Carregar informação da área e usuários

        return response()->json($equipment);
    }

    /**
     * Atualizar um equipamento de manutenção.
     *
     * @authenticated
     *
     * @urlParam equipment int required O ID do equipamento. Example: 1
     *
     * @bodyParam name string Nome do equipamento. Example: "Bomba Piscina Bloco B"
     * @bodyParam maintenance_area_id int ID da área de manutenção. Example: 2
     * @bodyParam description string Descrição do equipamento.
     * @bodyParam location_details string Detalhes da localização.
     * @bodyParam qr_code_access_level string Nível de acesso ao QR Code. Valores: `public`, `internal_users`, `specific_users`. Example: "public"
     *
     * @responseFile status=200 scenario="Atualizado com sucesso" storage/responses/maintenanceequipment.update.json
     * @responseFile status=400 scenario="Dados inválidos" storage/responses/validation.error.json
     * @responseFile status=404 scenario="Não encontrado" storage/responses/notfound.json
     */
    public function update(Request $request, MaintenanceEquipment $equipment)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (! $condominiumId && ! $request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($equipment->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a este equipamento.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'maintenance_area_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('maintenance_areas', 'id')->where(function ($query) use ($currentCondominiumId) {
                    $query->where('condominium_id', $currentCondominiumId);
                }),
            ],
            'description' => 'nullable|string',
            'location_details' => 'nullable|string|max:255',
            'qr_code_access_level' => ['sometimes', 'string', Rule::in(['public', 'internal_users', 'specific_users'])],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $equipment->update($request->only([
            'name',
            'maintenance_area_id',
            'description',
            'location_details',
            'qr_code_access_level',
        ]));

        $equipment->load(['maintenanceArea:id,name', 'qrAccessUsers:id,name']);

        return response()->json($equipment);
    }

    /**
     * Excluir um equipamento de manutenção.
     *
     * @authenticated
     *
     * @urlParam equipment int required O ID do equipamento. Example: 1
     *
     * @response status=204 scenario="Excluído com sucesso"
     *
     * @responseFile status=404 scenario="Não encontrado" storage/responses/notfound.json
     * @responseFile status=403 scenario="Proibido" storage/responses/forbidden.json
     */
    public function destroy(Request $request, MaintenanceEquipment $equipment)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (! $condominiumId && ! $request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($equipment->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a este equipamento.'], 403);
        }

        // Adicionar verificação se existem agendamentos/registros associados antes de excluir, se necessário
        if ($equipment->schedules()->exists()) {
            return response()->json(['message' => 'Não é possível excluir o equipamento pois existem agendamentos de manutenção associados.'], 400);
        }

        $equipment->delete();

        return response()->json(null, 204);
    }

    /**
     * Gerenciar usuários com acesso ao QR Code.
     *
     * Usado quando `qr_code_access_level` é `specific_users`.
     * Sincroniza a lista de usuários que podem acessar o relatório do QR Code.
     *
     * @authenticated
     *
     * @urlParam equipment int required O ID do equipamento. Example: 1
     *
     * @bodyParam user_ids array required Array de IDs de usuários. Example: [1, 2, 3]
     * @bodyParam user_ids.* int ID de um usuário. Example: 1
     *
     * @response status=200 scenario="Usuários atualizados" body={"message"="Usuários com acesso ao QR Code atualizados com sucesso."}
     *
     * @responseFile status=404 scenario="Equipamento não encontrado" storage/responses/notfound.json
     * @responseFile status=403 scenario="Proibido" storage/responses/forbidden.json
     * @responseFile status=400 scenario="Dados inválidos" storage/responses/validation.error.json
     */
    public function syncQrAccessUsers(Request $request, MaintenanceEquipment $equipment)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (! $condominiumId && ! $request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($equipment->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a este equipamento.'], 403);
        }

        // Permitir sincronização se for 'specific_users' OU se for um array vazio (limpeza durante mudança de nível)
        $userIds = $request->input('user_ids', []);
        $isClearing = empty($userIds);

        if ($equipment->qr_code_access_level !== 'specific_users' && ! $isClearing) {
            return response()->json(['message' => 'Este equipamento não está configurado para acesso específico de usuários.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(function ($query) use ($currentCondominiumId) {
                    // Garante que os usuários pertençam ao mesmo condomínio
                    $query->where('condominium_id', $currentCondominiumId);
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $equipment->qrAccessUsers()->sync($request->user_ids);

        return response()->json(['message' => 'Usuários com acesso ao QR Code atualizados com sucesso.']);
    }
}
