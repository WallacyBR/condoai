<?php

namespace App\Notifications;

use App\Models\MaintenanceSchedule;

class MaintenanceScheduleAssignedNotification extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly int $scheduleId,
        private readonly string $title,
        private readonly string $scheduleDates,
        private readonly string $dedupeKey,
        private readonly ?int $actorId,
    ) {}

    protected function buildPayload(): array
    {
        return $this->basePayload(
            $this->condominiumId,
            'maintenance',
            'Manutenção atribuída',
            "{$this->title} — {$this->scheduleDates}",
            "/maintenance/schedules/{$this->scheduleId}",
            $this->dedupeKey,
            $this->actorId,
            MaintenanceSchedule::class,
            $this->scheduleId,
        );
    }
}
