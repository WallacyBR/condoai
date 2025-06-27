<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * @group Manutenção - Relatório Público QR Code
 *
 * API para exibir relatório de manutenção via QR Code.
 */
class PublicMaintenanceReportController extends Controller
{
    /**
     * Relatório de Manutenção por QR Code.
     *
     * Exibe informações do equipamento, últimas manutenções realizadas e próxima agendada,
     * baseado no hash do QR Code e nas permissões de acesso configuradas.
     *
     * @urlParam qr_code_hash string required O hash identificador do QR Code do equipamento. Example: "a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4"
     *
     * @responseFile status=200 scenario="Sucesso (Público)" storage/responses/public.maintenance.report.json
     * @responseFile status=404 scenario="Equipamento não encontrado" storage/responses/notfound.json
     * @responseFile status=403 scenario="Acesso restrito" storage/responses/forbidden.json
     * @responseFile status=401 scenario="Requer autenticação" storage/responses/unauthenticated.json
     */
    public function showReportByQrCode(Request $request, $qr_code_hash)
    {
        $equipment = MaintenanceEquipment::where('qr_code_hash', $qr_code_hash)
                                         ->with(['condominium:id,name', 'maintenanceArea:id,name'])
                                         ->first();

        if (!$equipment) {
            return response()->json(['message' => 'Equipamento não encontrado ou QR Code inválido.'], 404);
        }

        $accessLevel = $equipment->qr_code_access_level;
        $user = Auth::guard('api')->user(); // Tenta pegar usuário autenticado pela API (se houver)

        if ($accessLevel === 'internal_users') {
            if (!$user) {
                return response()->json(['message' => 'Acesso restrito. Por favor, realize o login para visualizar este relatório.'], 401);
            }
            // Verifica se o usuário logado pertence ao mesmo condomínio do equipamento
            if ($user->condominium_id !== $equipment->condominium_id) {
                 return response()->json(['message' => 'Acesso não autorizado a este relatório.'], 403);
            }
        } elseif ($accessLevel === 'specific_users') {
            if (!$user) {
                return response()->json(['message' => 'Acesso restrito a usuários específicos. Por favor, realize o login.'], 401);
            }
            if ($user->condominium_id !== $equipment->condominium_id) {
                 return response()->json(['message' => 'Acesso não autorizado a este relatório.'], 403);
            }
            // Verifica se o usuário está na lista de permissão do equipamento
            if (!$equipment->qrAccessUsers()->where('user_id', $user->id)->exists()) {
                return response()->json(['message' => 'Você não tem permissão para visualizar este relatório específico.'], 403);
            }
        }
        // Se 'public', o acesso é permitido.

        // Buscar últimas 5 manutenções realizadas
        $lastRecords = MaintenanceRecord::whereHas('schedule', function ($query) use ($equipment) {
            $query->where('maintenance_equipment_id', $equipment->id);
        })
        ->where('status', 'completed')
        ->orderBy('performed_at', 'desc')
        ->take(5)
        ->with('user:id,name') // Quem realizou
        ->get(['id', 'performed_at', 'notes', 'user_id', 'status']);

        // Buscar próxima manutenção agendada.
        // ATENÇÃO: Esta lógica busca apenas o próximo *registro de manutenção pendente* existente.
        // Para agendamentos recorrentes, uma lógica mais robusta seria necessária para calcular
        // a *próxima ocorrência futura* baseada nas regras de recorrência do MaintenanceSchedule,
        // mesmo que ainda não haja um MaintenanceRecord gerado para ela.
        $nextPendingRecord = MaintenanceRecord::whereHas('schedule', function ($query) use ($equipment) {
            $query->where('maintenance_equipment_id', $equipment->id);
        })
        ->where('status', 'pending')
        ->whereHas('schedule', function($q){ // Garante que a data do schedule é futura
            $q->where('start_date', '>=', Carbon::today()->toDateString());
        })
        ->orderBy('maintenance_schedules.start_date', 'asc') // Ordena pela data do schedule
        ->select('maintenance_records.id', 'maintenance_records.status', 'maintenance_schedules.start_date', 'maintenance_schedules.due_time', 'maintenance_schedules.title')
        ->join('maintenance_schedules', 'maintenance_records.maintenance_schedule_id', '=', 'maintenance_schedules.id')
        ->first();


        return response()->json([
            'equipment' => [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'description' => $equipment->description,
                'location_details' => $equipment->location_details,
                'condominium_name' => $equipment->condominium->name,
                'maintenance_area_name' => $equipment->maintenanceArea->name,
            ],
            'last_maintenances' => $lastRecords,
            'next_scheduled_maintenance' => $nextPendingRecord ? [
                'record_id' => $nextPendingRecord->id,
                'title' => $nextPendingRecord->title,
                'status' => $nextPendingRecord->status,
                'scheduled_date' => Carbon::parse($nextPendingRecord->start_date)->format('d/m/Y'),
                'scheduled_time' => $nextPendingRecord->due_time ? Carbon::parse($nextPendingRecord->due_time)->format('H:i') : null,
            ] : null,
        ]);
    }
}
