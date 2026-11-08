<?php

namespace App\Listeners\Notifications;

use App\Events\Maintenance\MaintenanceRecordWasCompleted;
use App\Models\MaintenanceRecord;
use App\Notifications\MaintenanceRecordCompletedNotification;
use App\Services\Notifications\AudienceResolver;
use App\Services\Notifications\DedupeGuard;
use App\Support\RestoresTenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendMaintenanceRecordCompletedNotification implements ShouldQueue
{
    use InteractsWithQueue;
    use RestoresTenantContext;

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(MaintenanceRecordWasCompleted $event): void
    {
        $audienceResolver = app(AudienceResolver::class);
        $dedupeGuard = app(DedupeGuard::class);

        $this->withTenantContext($event->condominiumId, function () use ($event, $audienceResolver, $dedupeGuard): void {
            $record = MaintenanceRecord::query()->with('schedule')->findOrFail($event->maintenanceRecordId);

            if ($record->schedule === null) {
                return;
            }

            $users = $audienceResolver
                ->usersForMaintenanceRecordAudience($event->condominiumId, $record->schedule)
                ->reject(fn ($user) => $event->actorId !== null && (int) $user->id === (int) $event->actorId)
                ->unique('id');

            if ($users->isEmpty()) {
                return;
            }

            $scheduleTitle = $record->schedule->title;

            foreach ($users as $user) {
                $dedupeGuard->notifyIfNotDuplicate($user, new MaintenanceRecordCompletedNotification(
                    condominiumId: $event->condominiumId,
                    maintenanceRecordId: $record->id,
                    scheduleTitle: $scheduleTitle,
                    actorId: $event->actorId,
                ));
            }
        });
    }
}
