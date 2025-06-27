<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                                     ->with('equipment:id,name');

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
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->all();
        $data['condominium_id'] = $currentCondominiumId;
        if (!isset($data['is_recurring'])) {
            $data['is_recurring'] = false; // Default if not provided
        }

        $schedule = MaintenanceSchedule::create($data);

        return response()->json($schedule, 201);
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

        $schedule->load('equipment:id,name');
        return response()->json($schedule);
    }

    /**
     * Atualizar um agendamento de manutenção.
     *
     * @authenticated
     * @urlParam schedule int required O ID do agendamento. Example: 1
     * (Mesmos bodyParams do método store, mas como 'sometimes')
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
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $schedule->update($request->all());

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
}
