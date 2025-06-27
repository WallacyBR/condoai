<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceEquipment;
use App\Models\User; // Adicionado para validação de responsible_user_ids
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Adicionado para transação
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * @group Manutenção - Agendamentos
 *
 * APIs para gerenciar agendamentos de manutenção.
 */
class MaintenanceScheduleController extends Controller
{
    /**
     * Listar todos os agendamentos de manutenção.
     *
     * Retorna uma lista paginada dos agendamentos para o condomínio autenticado.
     * Pode ser filtrado por `maintenance_equipment_id`.
     *
     * @authenticated
     * @queryParam page int A página a ser retornada. Example: 1
     * @queryParam per_page int Número de itens por página. Example: 15
     * @queryParam maintenance_equipment_id int ID do equipamento para filtrar agendamentos. Example: 1
     *
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenanceschedules.index.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     * @responseFile status=400 scenario="Condomínio não identificado" storage/responses/validation.error.json
     */
    public function index(Request $request)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        $perPage = $request->input('per_page', 15);
        $query = MaintenanceSchedule::where('condominium_id', $currentCondominiumId)
                                     ->with(['equipment:id,name', 'responsibleUsers:id,name']); // Carregar usuários responsáveis

        if ($request->has('maintenance_equipment_id')) {
            $query->where('maintenance_equipment_id', $request->maintenance_equipment_id);
        }

        // Ordenar por data de início, depois por título
        $schedules = $query->orderBy('start_date', 'desc')->orderBy('title', 'asc')->paginate($perPage);
        return response()->json($schedules);
    }

    /**
     * Criar um novo agendamento de manutenção.
     *
     * @authenticated
     * @bodyParam title string required Título do agendamento. Example: "Verificação Semanal da Bomba X"
     * @bodyParam maintenance_equipment_id int required ID do equipamento. Example: 1
     * @bodyParam description string Descrição detalhada da manutenção.
     * @bodyParam type string required Tipo de agendamento. Valores: `daily`, `weekly`, `biweekly`, `monthly`, `semiannual`, `annual`, `specific_days`. Example: "weekly"
     * @bodyParam start_date date required Data de início do agendamento. Example: "2024-08-01"
     * @bodyParam end_date date Data de término (para agendamentos recorrentes com fim). Example: "2025-08-01"
     * @bodyParam due_time string Hora prevista para a manutenção (HH:MM). Example: "14:30"
     * @bodyParam is_recurring boolean Indica se o agendamento é recorrente. Default: false. Example: true
     * @bodyParam specific_days_of_week array Array de inteiros (0-6, Dom-Sab) para tipos `weekly` ou `specific_days`. Example: [1, 3, 5]
     * @bodyParam day_of_month int Dia do mês (1-31) para tipo `monthly`. Example: 15
     * @bodyParam month_of_year int Mês do ano (1-12) para tipos `semiannual` ou `annual`. Example: 3
     * @bodyParam responsible_user_ids array IDs dos usuários responsáveis. Example: [1, 2]
     *
     * @responseFile status=201 scenario="Criado com sucesso" storage/responses/maintenanceschedule.store.json
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
            'title' => 'required|string|max:255',
            'maintenance_equipment_id' => [
                'required',
                'integer',
                Rule::exists('maintenance_equipments', 'id')->where(function ($query) use ($currentCondominiumId) {
                    $query->where('condominium_id', $currentCondominiumId);
                }),
            ],
            'description' => 'nullable|string',
            'type' => ['required', 'string', Rule::in(['daily', 'weekly', 'biweekly', 'monthly', 'semiannual', 'annual', 'specific_days'])],
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'due_time' => 'nullable|date_format:H:i',
            'is_recurring' => 'sometimes|boolean',
            'specific_days_of_week' => 'nullable|array|required_if:type,weekly|required_if:type,specific_days',
            'specific_days_of_week.*' => 'integer|between:0,6', // Domingo (0) a Sábado (6)
            'day_of_month' => 'nullable|integer|between:1,31|required_if:type,monthly',
            'month_of_year' => 'nullable|integer|between:1,12|required_if:type,semiannual|required_if:type,annual',
            'responsible_user_ids' => 'nullable|array',
            'responsible_user_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(function ($query) use ($currentCondominiumId) {
                    // Garante que o usuário pertence ao mesmo condomínio ou é um usuário global (sem condomínio específico, se aplicável)
                    $query->where('condominium_id', $currentCondominiumId);
                    // Adicionar aqui outras condições de perfil se necessário, ex: whereIn('profile_id', [ID_ZELADOR, ID_COLABORADOR])
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->except('responsible_user_ids');
        $data['condominium_id'] = $currentCondominiumId;
        if (!isset($data['is_recurring'])) {
            $data['is_recurring'] = false; // Default if not provided
        }

        $schedule = null;
        DB::transaction(function () use ($data, $request, &$schedule) {
            $schedule = MaintenanceSchedule::create($data);

            if ($request->has('responsible_user_ids')) {
                $schedule->responsibleUsers()->sync($request->input('responsible_user_ids'));
            }
        });

        if ($schedule) {
            $schedule->load('responsibleUsers:id,name'); // Carregar para a resposta
            return response()->json($schedule, 201);
        } else {
            return response()->json(['message' => 'Erro ao criar agendamento.'], 500);
        }
    }

    /**
     * Exibir um agendamento de manutenção específico.
     *
     * @authenticated
     * @urlParam schedule int required O ID do agendamento. Example: 1
     *
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenanceschedule.show.json
     * @responseFile status=404 scenario="Não encontrado" storage/responses/notfound.json
     */
    public function show(Request $request, MaintenanceSchedule $schedule)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($schedule->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a este agendamento.'], 403);
        }

        $schedule->load(['equipment:id,name', 'responsibleUsers:id,name']); // Carregar usuários responsáveis
        return response()->json($schedule);
    }

    /**
     * Atualizar um agendamento de manutenção.
     *
     * @authenticated
     * @urlParam schedule int required O ID do agendamento. Example: 1
     * @bodyParam title string sometimes Título do agendamento. Example: "Nova Verificação da Bomba X"
     * @bodyParam maintenance_equipment_id int sometimes ID do equipamento. Example: 1
     * @bodyParam description string sometimes Descrição detalhada da manutenção.
     * @bodyParam type string sometimes Tipo de agendamento. Example: "monthly"
     * @bodyParam start_date date sometimes Data de início. Example: "2024-08-02"
     * @bodyParam end_date date sometimes Data de término. Example: "2025-08-02"
     * @bodyParam due_time string sometimes Hora prevista (HH:MM). Example: "15:00"
     * @bodyParam is_recurring boolean sometimes Indica se é recorrente. Example: true
     * @bodyParam specific_days_of_week array sometimes Array de inteiros (0-6) para tipos `weekly` ou `specific_days`. Example: [1, 3]
     * @bodyParam day_of_month int sometimes Dia do mês (1-31) para tipo `monthly`. Example: 10
     * @bodyParam month_of_year int sometimes Mês do ano (1-12) para tipos `semiannual` ou `annual`. Example: 4
     * @bodyParam responsible_user_ids array sometimes IDs dos usuários responsáveis. Example: [1, 3]
     *
     * @responseFile status=200 scenario="Atualizado com sucesso" storage/responses/maintenanceschedule.update.json
     * @responseFile status=400 scenario="Dados inválidos" storage/responses/validation.error.json
     * @responseFile status=404 scenario="Não encontrado" storage/responses/notfound.json
     */
    public function update(Request $request, MaintenanceSchedule $schedule)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($schedule->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a este agendamento.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'maintenance_equipment_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('maintenance_equipments', 'id')->where(function ($query) use ($currentCondominiumId) {
                    $query->where('condominium_id', $currentCondominiumId);
                }),
            ],
            'description' => 'nullable|string',
            'type' => ['sometimes', 'required', 'string', Rule::in(['daily', 'weekly', 'biweekly', 'monthly', 'semiannual', 'annual', 'specific_days'])],
            'start_date' => 'sometimes|required|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'due_time' => 'nullable|date_format:H:i',
            'is_recurring' => 'sometimes|boolean',
            'specific_days_of_week' => 'nullable|array|required_if:type,weekly|required_if:type,specific_days',
            'specific_days_of_week.*' => 'integer|between:0,6',
            'day_of_month' => 'nullable|integer|between:1,31|required_if:type,monthly',
            'month_of_year' => 'nullable|integer|between:1,12|required_if:type,semiannual|required_if:type,annual',
            'responsible_user_ids' => 'nullable|array',
            'responsible_user_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(function ($query) use ($currentCondominiumId) {
                    $query->where('condominium_id', $currentCondominiumId);
                     // Adicionar aqui outras condições de perfil se necessário
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        DB::transaction(function () use ($request, $schedule) {
            $schedule->update($request->except('responsible_user_ids'));

            if ($request->has('responsible_user_ids')) {
                $schedule->responsibleUsers()->sync($request->input('responsible_user_ids'));
            }
        });

        $schedule->load(['equipment:id,name', 'responsibleUsers:id,name']);
        return response()->json($schedule);
    }

    /**
     * Excluir um agendamento de manutenção.
     *
     * @authenticated
     * @urlParam schedule int required O ID do agendamento. Example: 1
     *
     * @response status=204 scenario="Excluído com sucesso"
     * @responseFile status=404 scenario="Não encontrado" storage/responses/notfound.json
     * @responseFile status=403 scenario="Proibido" storage/responses/forbidden.json
     */
    public function destroy(Request $request, MaintenanceSchedule $schedule)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($schedule->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a este agendamento.'], 403);
        }

        // Verificar se existem registros de manutenção associados antes de excluir
        if ($schedule->records()->exists()) {
            return response()->json(['message' => 'Não é possível excluir o agendamento pois existem registros de manutenção associados.'], 400);
        }

        $schedule->delete();

        return response()->json(null, 204);
    }

    /**
     * Listar os agendamentos de manutenção do usuário para uma data específica.
     *
     * Retorna uma lista de agendamentos atribuídos ao usuário autenticado
     * que estão programados para ocorrer na data especificada (ou hoje, por padrão).
     * A lógica de recorrência é aplicada para determinar se um agendamento está ativo na data.
     *
     * @group Manutenção - Agendamentos
     * @authenticated
     * @queryParam date string Data para a qual listar as tarefas. Formato YYYY-MM-DD. Se não fornecido, usa a data atual. Example: "2024-07-28"
     *
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenanceschedules.my_today.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     * @responseFile status=400 scenario="Data inválida ou Condomínio não identificado" storage/responses/validation.error.json
     */
    public function myTodaySchedules(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->condominium_id;

        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        $targetDateStr = $request->input('date', Carbon::today()->toDateString());
        try {
            $targetDate = Carbon::parse($targetDateStr)->startOfDay();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Formato de data inválido. Use YYYY-MM-DD.'], 400);
        }

        $schedules = MaintenanceSchedule::where('condominium_id', $currentCondominiumId)
            ->whereHas('responsibleUsers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['equipment:id,name', 'responsibleUsers:id,name'])
            ->get();

        $activeSchedulesForToday = $schedules->filter(function ($schedule) use ($targetDate) {
            $startDate = Carbon::parse($schedule->start_date)->startOfDay();
            $endDate = $schedule->end_date ? Carbon::parse($schedule->end_date)->endOfDay() : null;

            // Verifica se a data alvo está dentro do período do agendamento
            if ($targetDate->lt($startDate) || ($endDate && $targetDate->gt($endDate))) {
                return false;
            }

            if (!$schedule->is_recurring) {
                return $startDate->isSameDay($targetDate);
            }

            // Lógica para recorrência
            switch ($schedule->type) {
                case 'daily':
                    return true; // Se está no período, todo dia é válido
                case 'weekly':
                    // specific_days_of_week é 0 (Dom) a 6 (Sab)
                    // Carbon dayOfWeek é 0 (Dom) a 6 (Sab)
                    return !empty($schedule->specific_days_of_week) && in_array($targetDate->dayOfWeek, $schedule->specific_days_of_week);
                case 'biweekly': // A cada duas semanas
                    // Considera a semana do ano. Se a diferença de semanas desde o início é par.
                    // specific_days_of_week também se aplica aqui.
                    if (!empty($schedule->specific_days_of_week) && in_array($targetDate->dayOfWeek, $schedule->specific_days_of_week)) {
                        $weeksDifference = $startDate->diffInWeeks($targetDate);
                        return $weeksDifference % 2 === 0;
                    }
                    return false;
                case 'monthly':
                    // dia do mês (day_of_month)
                    return $targetDate->day == $schedule->day_of_month;
                case 'semiannual': // A cada 6 meses, no mês especificado (month_of_year) e dia (day_of_month)
                case 'annual':   // Uma vez por ano, no mês especificado (month_of_year) e dia (day_of_month)
                    // Para simplificar, vamos assumir que 'month_of_year' e 'day_of_month' são usados para 'semiannual' e 'annual'
                    // Para 'semiannual', pode ser o mês especificado ou o mês + 6.
                    if ($schedule->type === 'semiannual') {
                        $month1 = (int)$schedule->month_of_year;
                        $month2 = ($month1 + 6 -1) % 12 +1; // Normaliza para 1-12
                        return ($targetDate->month == $month1 || $targetDate->month == $month2) && $targetDate->day == $schedule->day_of_month;
                    }
                    // annual
                    return $targetDate->month == $schedule->month_of_year && $targetDate->day == $schedule->day_of_month;
                case 'specific_days':
                     // Já tratado pela checagem de specific_days_of_week e período.
                    return !empty($schedule->specific_days_of_week) && in_array($targetDate->dayOfWeek, $schedule->specific_days_of_week);

            }
            return false;
        });

        return response()->json($activeSchedulesForToday->values());
    }
}
