<?php

namespace App\Events\Maintenance;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceScheduleWasAssigned
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<int>  $addedUserIds
     */
    public function __construct(
        public readonly int $condominiumId,
        public readonly ?int $actorId,
        public readonly int $scheduleId,
        public readonly array $addedUserIds,
    ) {}
}
