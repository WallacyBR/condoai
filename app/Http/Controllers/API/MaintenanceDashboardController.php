<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @group Manutenção - Dashboard
 *
 * APIs para buscar dados para o dashboard de manutenção.
 */
class MaintenanceDashboardController extends Controller
{
    /**
     * Estatísticas Gerais do Dashboard de Manutenção.
     *
     * Retorna contagens de manutenções vencidas, realizadas e pendentes (a realizar).
     *
     * @authenticated
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenance.dashboard.stats.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     */
    public function getGeneralStats(Request $request)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;
        $now = Carbon::now();

        // Manutenções Vencidas: Registros com status 'overdue'
        // OU registros 'pending' cuja data de execução esperada já passou.
        // A data de execução esperada é a 'start_date' do schedule. Se for recorrente,
        // precisamos de uma lógica mais elaborada para calcular as ocorrências futuras/passadas.
        // Por simplicidade inicial, vamos considerar 'overdue' os explicitamente marcados
        // e 'pending' com 'start_date' no passado.
        // Uma lógica mais robusta para 'overdue' em agendamentos recorrentes será necessária.

        $overdueCount = MaintenanceRecord::where('condominium_id', $currentCondominiumId)
            ->where(function ($query) use ($now) {
                $query->where('status', 'overdue')
                      ->orWhere(function ($subQuery) use ($now) {
                          $subQuery->where('status', 'pending')
                                   ->whereHas('schedule', function ($sQuery) use ($now) {
                                       // Considera a start_date do schedule para registros pendentes
                                       // Se due_time existir, compara com a hora atual também
                                       $sQuery->where(function($q) use ($now) {
                                           $q->where('start_date', '<', $now->toDateString())
                                             ->orWhere(function($q2) use ($now){
                                                $q2->where('start_date', '=', $now->toDateString())
                                                   ->whereNotNull('due_time')
                                                   ->where('due_time', '<', $now->toTimeString());
                                             });
                                       });
                                   });
                      });
            })->count();

        $completedCount = MaintenanceRecord::where('condominium_id', $currentCondominiumId)
            ->where('status', 'completed')
            // Poderia adicionar um filtro de período, ex: nos últimos 30 dias
            // ->where('performed_at', '>=', Carbon::now()->subDays(30))
            ->count();

        // Manutenções Pendentes (A Realizar): Registros com status 'pending' e data futura
        $pendingCount = MaintenanceRecord::where('condominium_id', $currentCondominiumId)
            ->where('status', 'pending')
            ->whereHas('schedule', function ($sQuery) use ($now) {
                $sQuery->where(function($q) use ($now) {
                    $q->where('start_date', '>', $now->toDateString())
                      ->orWhere(function($q2) use ($now){
                         $q2->where('start_date', '=', $now->toDateString())
                            ->where(function ($q3) use ($now) { // Se tem due_time, deve ser futuro, senão qualquer um no dia de hoje
                                $q3->whereNull('due_time')
                                   ->orWhere('due_time', '>=', $now->toTimeString());
                            });
                      });
                });
            })
            ->count();

        // Total de Equipamentos (para referência no dashboard, talvez)
        $totalEquipment = \App\Models\MaintenanceEquipment::where('condominium_id', $currentCondominiumId)->count();

        return response()->json([
            'overdue_maintenance' => $overdueCount,
            'completed_maintenance' => $completedCount,
            'pending_maintenance' => $pendingCount,
            'total_equipment' => $totalEquipment,
            // Poderíamos adicionar "Manutenções em Revisão"
            'under_review_maintenance' => MaintenanceRecord::where('condominium_id', $currentCondominiumId)->where('status', 'under_review')->count(),
        ]);
    }


    /**
     * Estatísticas de Manutenção por Equipamento.
     *
     * Retorna contagens de manutenções vencidas, realizadas e pendentes, agrupadas por equipamento.
     *
     * @authenticated
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenance.dashboard.byequipment.json
     */
    public function getStatsByEquipment(Request $request)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;
        $now = Carbon::now();

        $stats = \App\Models\MaintenanceEquipment::where('condominium_id', $currentCondominiumId)
            ->withCount([
                'schedules as total_schedules_count',
                'schedules as pending_schedules_count' => function ($query) use ($now) {
                    $query->whereHas('records', function($q) use ($now) {
                        $q->where('status', 'pending')
                          ->whereHas('schedule', function ($sQuery) use ($now) {
                                $sQuery->where(function($sq) use ($now) {
                                    $sq->where('start_date', '>', $now->toDateString())
                                      ->orWhere(function($sq2) use ($now){
                                         $sq2->where('start_date', '=', $now->toDateString())
                                            ->where(function ($sq3) use ($now) {
                                                $sq3->whereNull('due_time')
                                                   ->orWhere('due_time', '>=', $now->toTimeString());
                                            });
                                      });
                                });
                            });
                    });
                },
                'schedules as completed_schedules_count' => function ($query) {
                    $query->whereHas('records', function($q){
                        $q->where('status', 'completed');
                    });
                },
                'schedules as overdue_schedules_count' => function ($query) use ($now) {
                     $query->whereHas('records', function($q) use ($now) {
                        $q->where('status', 'overdue')
                          ->orWhere(function ($subQuery) use ($now) {
                              $subQuery->where('status', 'pending')
                                       ->whereHas('schedule', function ($sQuery) use ($now) {
                                           $sQuery->where(function($sq) use ($now) {
                                               $sq->where('start_date', '<', $now->toDateString())
                                                 ->orWhere(function($sq2) use ($now){
                                                    $sq2->where('start_date', '=', $now->toDateString())
                                                       ->whereNotNull('due_time')
                                                       ->where('due_time', '<', $now->toTimeString());
                                                 });
                                           });
                                       });
                          });
                    });
                }
            ])
            ->select('id', 'name', 'maintenance_area_id') // Selecionar campos necessários
            ->with('maintenanceArea:id,name') // Carregar a área para contexto
            ->orderBy('name')
            ->get();

        return response()->json($stats);
    }

    /**
     * Estatísticas de Manutenção por Área.
     *
     * Retorna contagens de manutenções vencidas, realizadas e pendentes, agrupadas por área de manutenção.
     *
     * @authenticated
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenance.dashboard.byarea.json
     */
    public function getStatsByArea(Request $request)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;
        $now = Carbon::now();

        $stats = \App\Models\MaintenanceArea::where('condominium_id', $currentCondominiumId)
            ->select('id', 'name')
            ->withCount([
                'equipments',
                'equipments as pending_schedules_count' => function ($query) use ($now, $currentCondominiumId) {
                    $query->whereHas('schedules.records', function ($q) use ($now) {
                        $q->where('status', 'pending')
                            ->whereHas('schedule', function ($sQuery) use ($now) {
                                $sQuery->where(function ($sq) use ($now) {
                                    $sq->where('start_date', '>', $now->toDateString())
                                        ->orWhere(function ($sq2) use ($now) {
                                            $sq2->where('start_date', '=', $now->toDateString())
                                                ->where(function ($sq3) use ($now) {
                                                    $sq3->whereNull('due_time')
                                                        ->orWhere('due_time', '>=', $now->toTimeString());
                                                });
                                        });
                                });
                            });
                    });
                },
                'equipments as completed_schedules_count' => function ($query) {
                    $query->whereHas('schedules.records', function ($q) {
                        $q->where('status', 'completed');
                    });
                },
                'equipments as overdue_schedules_count' => function ($query) use ($now) {
                    $query->whereHas('schedules.records', function ($q) use ($now) {
                        $q->where('status', 'overdue')
                            ->orWhere(function ($subQuery) use ($now) {
                                $subQuery->where('status', 'pending')
                                    ->whereHas('schedule', function ($sQuery) use ($now) {
                                        $sQuery->where(function ($sq) use ($now) {
                                            $sq->where('start_date', '<', $now->toDateString())
                                                ->orWhere(function ($sq2) use ($now) {
                                                    $sq2->where('start_date', '=', $now->toDateString())
                                                        ->whereNotNull('due_time')
                                                        ->where('due_time', '<', $now->toTimeString());
                                                });
                                        });
                                    });
                            });
                    });
                }
            ])
            ->orderBy('name')
            ->get();

        return response()->json($stats);
    }
}
