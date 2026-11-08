<?php

namespace App\Notifications;

use App\Models\MaintenanceRecord;

class MaintenanceRecordReopenedNotification extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly int $maintenanceRecordId,
        private readonly string $scheduleTitle,
        private readonly ?int $actorId,
    ) {}

    protected function buildPayload(): array
    {
        return $this->basePayload(
            $this->condominiumId,
            'maintenance',
            'Manutenção reaberta',
            $this->scheduleTitle,
            "/maintenance/records/{$this->maintenanceRecordId}",
            "maintenance_record.reopened:{$this->maintenanceRecordId}",
            $this->actorId,
            MaintenanceRecord::class,
            $this->maintenanceRecordId,
        );
    }
}
