<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceRecordPhoto;
use App\Models\MaintenanceRecordSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * @group Manutenção - Registros
 *
 * APIs para gerenciar registros de realização de manutenções.
 */
class MaintenanceRecordController extends Controller
{
    /**
     * Listar todos os registros de manutenção.
     *
     * Retorna uma lista paginada dos registros para o condomínio autenticado.
     * Pode ser filtrado por `maintenance_schedule_id` ou `maintenance_equipment_id`.
     *
     * @authenticated
     * @queryParam page int A página a ser retornada. Example: 1
     * @queryParam per_page int Número de itens por página. Example: 15
     * @queryParam maintenance_schedule_id int ID do agendamento para filtrar registros. Example: 1
     * @queryParam maintenance_equipment_id int ID do equipamento para filtrar registros. Example: 1
     * @queryParam status string Status do registro para filtrar. Example: "pending"
     *
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenancerecords.index.json
     */
    public function index(Request $request)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        $perPage = $request->input('per_page', 15);
        $query = MaintenanceRecord::where('condominium_id', $currentCondominiumId)
                                    ->with(['schedule:id,title', 'user:id,name', 'reviewer:id,name', 'equipment:id,name']); // equipment via schedule

        if ($request->has('maintenance_schedule_id')) {
            $query->where('maintenance_schedule_id', $request->maintenance_schedule_id);
        }
        if ($request->has('maintenance_equipment_id')) {
            $equipmentId = $request->maintenance_equipment_id;
            $query->whereHas('schedule', function ($q) use ($equipmentId) {
                $q->where('maintenance_equipment_id', $equipmentId);
            });
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->orderBy('performed_at', 'desc')->orderBy('created_at', 'desc')->paginate($perPage);

        // Adicionar nome do equipamento ao resultado
        $records->getCollection()->transform(function ($record) {
            if ($record->schedule && $record->schedule->equipment) {
                $record->equipment_name = $record->schedule->equipment->name;
            } else {
                $record->equipment_name = null;
            }
            // Remover o carregamento aninhado para não poluir o JSON final se não for necessário
            // unset($record->schedule->equipment);
            return $record;
        });


        return response()->json($records);
    }

    /**
     * Criar um novo registro de manutenção.
     *
     * @authenticated
     * @bodyParam maintenance_schedule_id int required ID do agendamento. Example: 1
     * @bodyParam user_id int ID do usuário que realizou (se diferente do autenticado, requer permissão). Example: 1
     * @bodyParam performed_at datetime required Data e hora da realização. Example: "2024-07-15 14:00:00"
     * @bodyParam notes string Notas sobre a manutenção.
     * @bodyParam status string Status do registro. Valores: `pending`, `completed`, `overdue`, `under_review`, `reopened`, `archived`. Default: `pending`. Example: "completed"
     * @bodyParam photos file[] Array de fotos da manutenção. Max 5MB cada.
     * @bodyParam signature string Imagem da assinatura em base64.
     *
     * @responseFile status=201 scenario="Criado com sucesso" storage/responses/maintenancerecord.store.json
     * @responseFile status=400 scenario="Dados inválidos" storage/responses/validation.error.json
     */
    public function store(Request $request)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        $validator = Validator::make($request->all(), [
            'maintenance_schedule_id' => [
                'required',
                'integer',
                Rule::exists('maintenance_schedules', 'id')->where(function ($query) use ($currentCondominiumId) {
                    $query->where('condominium_id', $currentCondominiumId);
                }),
            ],
            'user_id' => 'sometimes|integer|exists:users,id', // Validar se usuário pertence ao condomínio se admin puder setar
            'performed_at' => 'required|date_format:Y-m-d H:i:s',
            'notes' => 'nullable|string',
            'status' => ['sometimes', 'string', Rule::in(['pending', 'completed', 'overdue', 'under_review', 'reopened', 'archived'])],
            'photos' => 'nullable|array|max:5', // Max 5 photos
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:5120', // Max 5MB per photo
            'signature' => 'nullable|string', // Base64 encoded image
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->only(['maintenance_schedule_id', 'performed_at', 'notes', 'status']);
        $data['condominium_id'] = $currentCondominiumId;
        $data['user_id'] = $request->input('user_id', Auth::id()); // Default to authenticated user
        if (!$request->has('status')) {
            $data['status'] = 'completed'; // Default status if performed
        }


        $record = MaintenanceRecord::create($data);

        // Handle Photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photoFile) {
                $path = $photoFile->store("maintenance_photos/condo_{$currentCondominiumId}/record_{$record->id}", 'public');
                MaintenanceRecordPhoto::create([
                    'maintenance_record_id' => $record->id,
                    'file_path' => $path,
                    'condominium_id' => $currentCondominiumId,
                    'uploaded_at' => now(),
                ]);
            }
        }

        // Handle Signature
        if ($request->filled('signature')) {
            $signatureData = $request->input('signature'); // base64 string
            // Remove "data:image/png;base64,"
            if (strpos($signatureData, 'data:image/png;base64,') === 0) {
                $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
                $signatureData = str_replace(' ', '+', $signatureData);
                $signatureImage = base64_decode($signatureData);
                $signatureFileName = "signature_record_{$record->id}_" . time() . '.png';
                $path = "maintenance_signatures/condo_{$currentCondominiumId}/{$signatureFileName}";
                Storage::disk('public')->put($path, $signatureImage);

                MaintenanceRecordSignature::create([
                    'maintenance_record_id' => $record->id,
                    'signature_path' => $path,
                    'condominium_id' => $currentCondominiumId,
                    'signed_at' => now(),
                ]);
            }
        }

        $record->load(['photos', 'signature', 'user:id,name', 'schedule:id,title']);
        return response()->json($record, 201);
    }

    /**
     * Exibir um registro de manutenção específico.
     *
     * @authenticated
     * @urlParam record int required O ID do registro. Example: 1
     *
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenancerecord.show.json
     * @responseFile status=404 scenario="Não encontrado" storage/responses/notfound.json
     */
    public function show(Request $request, MaintenanceRecord $record)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($record->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a este registro.'], 403);
        }

        $record->load(['schedule:id,title,maintenance_equipment_id', 'user:id,name', 'reviewer:id,name', 'photos', 'signature', 'schedule.equipment:id,name']);
        return response()->json($record);
    }

    /**
     * Atualizar um registro de manutenção (principalmente status ou notas).
     *
     * @authenticated
     * @urlParam record int required O ID do registro. Example: 1
     * @bodyParam notes string Notas sobre a manutenção.
     * @bodyParam status string Status do registro. Example: "completed"
     * @bodyParam performed_at datetime Data e hora da realização. Example: "2024-07-15 14:30:00"
     *
     * @responseFile status=200 scenario="Atualizado com sucesso" storage/responses/maintenancerecord.update.json
     * @responseFile status=400 scenario="Dados inválidos" storage/responses/validation.error.json
     */
    public function update(Request $request, MaintenanceRecord $record)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($record->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a este registro.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'status' => ['sometimes', 'required', 'string', Rule::in(['pending', 'completed', 'overdue', 'under_review', 'reopened', 'archived'])],
            'performed_at' => 'sometimes|nullable|date_format:Y-m-d H:i:s',
            // Adicionar validação para reviewed_by_user_id e reviewed_at se forem atualizáveis aqui
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $updateData = $request->only(['notes', 'status', 'performed_at']);

        // Se o status está sendo mudado para 'under_review', 'reopened', 'archived' por um usuário diferente do que realizou,
        // ou se um admin está mudando, pode ser necessário lógica adicional de permissão ou log.
        // Se o status for 'completed' e não houver 'performed_at', preencher com 'now()'.
        if ($request->input('status') === 'completed' && empty($record->performed_at) && empty($request->input('performed_at'))) {
            $updateData['performed_at'] = now();
        }


        $record->update($updateData);
        $record->load(['photos', 'signature', 'user:id,name', 'schedule:id,title']);
        return response()->json($record);
    }

    /**
     * Excluir um registro de manutenção.
     *
     * Normalmente, registros são arquivados, não excluídos.
     * Considere se esta rota é realmente necessária ou se deve apenas permitir arquivamento.
     *
     * @authenticated
     * @urlParam record int required O ID do registro. Example: 1
     *
     * @response status=204 scenario="Excluído com sucesso"
     */
    public function destroy(Request $request, MaintenanceRecord $record)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($record->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a este registro.'], 403);
        }

        // Antes de deletar, remover arquivos associados (fotos, assinatura)
        foreach($record->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
            $photo->delete();
        }
        if ($record->signature) {
            Storage::disk('public')->delete($record->signature->signature_path);
            $record->signature->delete();
        }

        $record->delete();

        return response()->json(null, 204);
    }

    /**
     * Atualizar o status de um registro de manutenção (conferência, reabertura, etc.).
     *
     * @authenticated
     * @urlParam record int required O ID do registro. Example: 1
     * @bodyParam status string required Novo status. Valores: `under_review`, `reopened`, `archived`, `completed`. Example: "under_review"
     * @bodyParam review_notes string Notas da revisão (opcional).
     *
     * @responseFile status=200 scenario="Status atualizado" storage/responses/maintenancerecord.status.json
     * @responseFile status=400 scenario="Dados inválidos" storage/responses/validation.error.json
     */
    public function updateStatus(Request $request, MaintenanceRecord $record)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($record->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a este registro.'], 403);
        }

        $validStatuses = ['under_review', 'reopened', 'archived', 'completed', 'pending']; // Adicionado pending
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', Rule::in($validStatuses)],
            'review_notes' => 'nullable|string', // Poderia ser usado para notas de reabertura, arquivamento, etc.
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $newStatus = $request->input('status');
        $updateData = ['status' => $newStatus];

        // Lógica específica para cada status
        if ($newStatus === 'under_review') {
            $updateData['reviewed_by_user_id'] = Auth::id();
            $updateData['reviewed_at'] = now();
        } elseif ($newStatus === 'reopened') {
            // Limpar dados de revisão anterior se estiver reabrindo
            $updateData['reviewed_by_user_id'] = null;
            $updateData['reviewed_at'] = null;
            // Notas de reabertura podem ser adicionadas às notas principais ou a um campo específico
        } elseif ($newStatus === 'completed') {
            if (empty($record->performed_at)) { // Se estava pendente/reaberto e agora completo
                 $updateData['performed_at'] = now();
            }
            // Se já foi revisado e está sendo marcado como completo após revisão, manter dados da revisão.
            // Se estava 'under_review' e um usuário diferente do revisor marca como 'completed',
            // pode-se limpar a revisão ou manter, dependendo da regra de negócio.
            // Por ora, vamos assumir que completar após revisão não limpa a revisão.
        }

        // Adicionar notas de revisão se fornecidas
        if ($request->filled('review_notes') && !empty($request->input('review_notes'))) {
            $updateData['notes'] = ($record->notes ? $record->notes . "\n" : '') . "Nota de Revisão/Status (" . Auth::user()->name . " em " . now()->format('d/m/Y H:i') . "): " . $request->input('review_notes');
        }

        $record->update($updateData);
        $record->load(['photos', 'signature', 'user:id,name', 'reviewer:id,name', 'schedule:id,title']);
        return response()->json($record);
    }
}
