<?php

namespace App\Http\Controllers\API;

use App\Events\Maintenance\MaintenanceScheduleWasAssigned;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @group Manutenção - Agendamentos
 *
 * APIs para gerenciar agendamentos de manutenção.
 */
class MaintenanceScheduleController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);
        $perPage = $request->input('per_page', 15);

        $query = MaintenanceSchedule::query()
            ->with(['equipment:id,name', 'responsibleUsers:id,name']);

        if ($request->has('maintenance_equipment_id')) {
            $query->where('maintenance_equipment_id', $request->maintenance_equipment_id);
        }

        return response()->json(
            $query->orderBy('start_date', 'desc')->orderBy('title', 'asc')->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'maintenance_equipment_id' => [
                'required',
                'integer',
                Rule::exists('maintenance_equipments', 'id')->where(fn ($query) => $query->where('condominium_id', $condominiumId)),
            ],
            'description' => 'nullable|string',
            'type' => ['required', 'string', Rule::in(['daily', 'weekly', 'biweekly', 'monthly', 'semiannual', 'annual', 'specific_days'])],
            'start_date' => 'required|date_format:Y-m-d',
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
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('condominium_id', $condominiumId)),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->except('responsible_user_ids', 'condominium_id');
        $data['condominium_id'] = $condominiumId;
        if (! isset($data['is_recurring'])) {
            $data['is_recurring'] = false;
        }

        $schedule = null;
        $addedUserIds = [];
        DB::transaction(function () use ($data, $request, &$schedule, &$addedUserIds) {
            $schedule = MaintenanceSchedule::create($data);

            if ($request->has('responsible_user_ids')) {
                $addedUserIds = array_values(array_map('intval', $request->input('responsible_user_ids', [])));
                $schedule->responsibleUsers()->sync($addedUserIds);
            }
        });

        if ($schedule) {
            if ($addedUserIds !== []) {
                event(new MaintenanceScheduleWasAssigned(
                    condominiumId: $condominiumId,
                    actorId: Auth::id(),
                    scheduleId: $schedule->id,
                    addedUserIds: $addedUserIds,
                ));
            }

            $schedule->load('responsibleUsers:id,name');

            return response()->json($schedule, 201);
        }

        return response()->json(['message' => 'Erro ao criar agendamento.'], 500);
    }

    public function show(MaintenanceSchedule $schedule)
    {
        $schedule->load(['equipment:id,name', 'responsibleUsers:id,name']);

        return response()->json($schedule);
    }

    public function update(Request $request, MaintenanceSchedule $schedule)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'maintenance_equipment_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('maintenance_equipments', 'id')->where(fn ($query) => $query->where('condominium_id', $condominiumId)),
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
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('condominium_id', $condominiumId)),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $addedUserIds = [];
        DB::transaction(function () use ($request, $schedule, &$addedUserIds) {
            $schedule->update($request->except('responsible_user_ids', 'condominium_id'));

            if ($request->has('responsible_user_ids')) {
                $previousUserIds = $schedule->responsibleUsers()->pluck('users.id')->all();
                $newUserIds = array_values(array_map('intval', $request->input('responsible_user_ids', [])));
                $addedUserIds = array_values(array_diff($newUserIds, $previousUserIds));
                $schedule->responsibleUsers()->sync($newUserIds);
            }
        });

        if ($addedUserIds !== []) {
            event(new MaintenanceScheduleWasAssigned(
                condominiumId: $condominiumId,
                actorId: Auth::id(),
                scheduleId: $schedule->id,
                addedUserIds: $addedUserIds,
            ));
        }

        $schedule->load(['equipment:id,name', 'responsibleUsers:id,name']);

        return response()->json($schedule);
    }

    public function destroy(MaintenanceSchedule $schedule)
    {
        if ($schedule->records()->exists()) {
            return response()->json(['message' => 'Não é possível excluir o agendamento pois existem registros de manutenção associados.'], 400);
        }

        $schedule->delete();

        return response()->json(null, 204);
    }

    public function myTodaySchedules(Request $request)
    {
        $this->tenantCondominiumId($request);
        $user = Auth::user();

        $targetDateStr = $request->input('date', Carbon::today()->toDateString());
        try {
            $targetDate = Carbon::parse($targetDateStr)->startOfDay();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Formato de data inválido. Use YYYY-MM-DD.'], 400);
        }

        $schedules = MaintenanceSchedule::query()
            ->whereHas('responsibleUsers', fn ($query) => $query->where('user_id', $user->id))
            ->with(['equipment:id,name', 'responsibleUsers:id,name'])
            ->get();

        $activeSchedulesForToday = $schedules->filter(function ($schedule) use ($targetDate) {
            $startDate = Carbon::parse($schedule->start_date)->startOfDay();
            $endDate = $schedule->end_date ? Carbon::parse($schedule->end_date)->endOfDay() : null;

            if ($targetDate->lt($startDate) || ($endDate && $targetDate->gt($endDate))) {
                return false;
            }

            if (! $schedule->is_recurring) {
                return $startDate->isSameDay($targetDate);
            }

            switch ($schedule->type) {
                case 'daily':
                    return true;
                case 'weekly':
                    return ! empty($schedule->specific_days_of_week) && in_array($targetDate->dayOfWeek, $schedule->specific_days_of_week);
                case 'biweekly':
                    if (! empty($schedule->specific_days_of_week) && in_array($targetDate->dayOfWeek, $schedule->specific_days_of_week)) {
                        $weeksDifference = $startDate->diffInWeeks($targetDate);

                        return $weeksDifference % 2 === 0;
                    }

                    return false;
                case 'monthly':
                    return $targetDate->day == $schedule->day_of_month;
                case 'semiannual':
                    $month1 = (int) $schedule->month_of_year;
                    $month2 = ($month1 + 6 - 1) % 12 + 1;

                    return ($targetDate->month == $month1 || $targetDate->month == $month2) && $targetDate->day == $schedule->day_of_month;
                case 'annual':
                    return $targetDate->month == $schedule->month_of_year && $targetDate->day == $schedule->day_of_month;
                case 'specific_days':
                    return ! empty($schedule->specific_days_of_week) && in_array($targetDate->dayOfWeek, $schedule->specific_days_of_week);
            }

            return false;
        });

        $schedulesWithStatus = $activeSchedulesForToday->map(function ($schedule) use ($targetDate) {
            $completedRecord = $schedule->records()
                ->where('status', 'completed')
                ->where(function ($query) use ($targetDate) {
                    $query->whereDate('performed_at', $targetDate->toDateString())
                        ->orWhere(function ($subQuery) use ($targetDate) {
                            $subQuery->whereDate('created_at', $targetDate->toDateString())
                                ->whereNotNull('performed_at');
                        });
                })
                ->orderBy('performed_at', 'desc')
                ->first();

            $schedule->is_completed_today = ! is_null($completedRecord);
            $schedule->completed_record_id = $completedRecord ? $completedRecord->id : null;
            $schedule->completed_at = $completedRecord ? $completedRecord->performed_at : null;
            $schedule->completion_notes = $completedRecord ? $completedRecord->notes : null;

            return $schedule;
        });

        return response()->json($schedulesWithStatus->values());
    }

    public function refreshTodaySchedules(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);
        $user = Auth::user();

        $targetDateStr = $request->input('date', Carbon::today()->toDateString());
        try {
            $targetDate = Carbon::parse($targetDateStr)->startOfDay();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Formato de data inválido. Use YYYY-MM-DD.'], 400);
        }

        \Cache::forget("my_today_schedules_{$user->id}_{$targetDate->toDateString()}");

        $userRecords = MaintenanceRecord::query()
            ->where('user_id', $user->id)
            ->where(function ($query) use ($targetDate) {
                $query->whereDate('performed_at', $targetDate->toDateString())
                    ->orWhereDate('created_at', $targetDate->toDateString());
            })
            ->with('schedule:id,title')
            ->get();

        return response()->json([
            'message' => 'Status das tarefas verificado com sucesso',
            'target_date' => $targetDate->toDateString(),
            'records_found' => $userRecords->count(),
            'records' => $userRecords->map(function ($record) {
                return [
                    'id' => $record->id,
                    'schedule_id' => $record->maintenance_schedule_id,
                    'schedule_title' => $record->schedule->title ?? 'N/A',
                    'status' => $record->status,
                    'performed_at' => $record->performed_at?->format('Y-m-d H:i:s'),
                    'created_at' => $record->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    public function myCompletedRecords(Request $request)
    {
        $this->tenantCondominiumId($request);
        $user = Auth::user();
        $perPage = $request->input('per_page', 15);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && ! Carbon::createFromFormat('Y-m-d', $startDate)) {
            return response()->json(['message' => 'Formato de start_date inválido. Use YYYY-MM-DD.'], 400);
        }

        if ($endDate && ! Carbon::createFromFormat('Y-m-d', $endDate)) {
            return response()->json(['message' => 'Formato de end_date inválido. Use YYYY-MM-DD.'], 400);
        }

        $query = MaintenanceRecord::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->with([
                'schedule:id,title,maintenance_equipment_id,description',
                'schedule.equipment:id,name',
                'photos:id,maintenance_record_id,photo_path',
                'signature:id,maintenance_record_id,signature_path',
            ]);

        if ($startDate) {
            $query->whereDate('performed_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('performed_at', '<=', $endDate);
        }

        $records = $query->orderBy('performed_at', 'desc')->paginate($perPage);

        $records->getCollection()->transform(function ($record) {
            if ($record->schedule && $record->schedule->equipment) {
                $record->equipment_name = $record->schedule->equipment->name;
                $record->schedule_title = $record->schedule->title;
            }

            return $record;
        });

        return response()->json($records);
    }
}
