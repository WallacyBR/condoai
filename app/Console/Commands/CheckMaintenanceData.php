<?php

namespace App\Console\Commands;

use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckMaintenanceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:check-data {user_id} {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica os dados de manutenção para um usuário específico e data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $targetDate = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();

        $this->info("Verificando dados de manutenção para usuário {$userId} na data {$targetDate->format('Y-m-d')}");

        // Buscar agendamentos do usuário
        $schedules = MaintenanceSchedule::whereHas('responsibleUsers', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with(['equipment:id,name', 'responsibleUsers:id,name'])
            ->get();

        $this->info('Encontrados '.$schedules->count().' agendamentos para o usuário');

        foreach ($schedules as $schedule) {
            $this->line('─────────────────────────────────────');
            $this->info("Agendamento: {$schedule->title} (ID: {$schedule->id})");
            $this->info("Equipamento: {$schedule->equipment->name}");
            $this->info("Tipo: {$schedule->type}, Recorrente: ".($schedule->is_recurring ? 'Sim' : 'Não'));

            // Verificar se está ativo na data
            $startDate = Carbon::parse($schedule->start_date)->startOfDay();
            $endDate = $schedule->end_date ? Carbon::parse($schedule->end_date)->endOfDay() : null;

            $isInPeriod = $targetDate->gte($startDate) && (! $endDate || $targetDate->lte($endDate));
            $this->info('Está no período? '.($isInPeriod ? 'Sim' : 'Não'));

            if ($isInPeriod) {
                // Verificar se deve ocorrer na data
                $shouldOccur = $this->shouldScheduleOccurOnDate($schedule, $targetDate);
                $this->info('Deve ocorrer na data? '.($shouldOccur ? 'Sim' : 'Não'));

                if ($shouldOccur) {
                    // Verificar registros de execução
                    $records = MaintenanceRecord::where('maintenance_schedule_id', $schedule->id)
                        ->whereDate('performed_at', $targetDate->toDateString())
                        ->get();

                    $this->info('Registros encontrados para a data: '.$records->count());

                    foreach ($records as $record) {
                        $this->info("  - Registro ID: {$record->id}, Status: {$record->status}, Executado em: {$record->performed_at}");
                    }

                    $completedRecord = $records->where('status', 'completed')->first();
                    $this->info('Registro concluído encontrado? '.($completedRecord ? "Sim (ID: {$completedRecord->id})" : 'Não'));
                }
            }
        }

        $this->line('─────────────────────────────────────');
        $this->info('Verificação concluída!');
    }

    private function shouldScheduleOccurOnDate($schedule, $targetDate)
    {
        if (! $schedule->is_recurring) {
            $startDate = Carbon::parse($schedule->start_date)->startOfDay();

            return $startDate->isSameDay($targetDate);
        }

        // Lógica para recorrência
        switch ($schedule->type) {
            case 'daily':
                return true;
            case 'weekly':
                return ! empty($schedule->specific_days_of_week) && in_array($targetDate->dayOfWeek, $schedule->specific_days_of_week);
            case 'biweekly':
                if (! empty($schedule->specific_days_of_week) && in_array($targetDate->dayOfWeek, $schedule->specific_days_of_week)) {
                    $startDate = Carbon::parse($schedule->start_date)->startOfDay();
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
    }
}
