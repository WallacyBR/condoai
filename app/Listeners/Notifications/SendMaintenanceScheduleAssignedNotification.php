<?php

namespace App\Listeners\Notifications;

use App\Events\Maintenance\MaintenanceScheduleWasAssigned;
use App\Models\MaintenanceSchedule;
use App\Notifications\MaintenanceScheduleAssignedNotification;
use App\Services\Notifications\AudienceResolver;
use App\Services\Notifications\DedupeGuard;
use App\Support\RestoresTenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendMaintenanceScheduleAssignedNotification implements ShouldQueue
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

    public function handle(MaintenanceScheduleWasAssigned $event): void
    {
        $audienceResolver = app(AudienceResolver::class);
        $dedupeGuard = app(DedupeGuard::class);

        $this->withTenantContext($event->condominiumId, function () use ($event, $audienceResolver, $dedupeGuard): void {
            if ($event->addedUserIds === []) {
                return;
            }

            $schedule = MaintenanceSchedule::query()->with('equipment')->findOrFail($event->scheduleId);

            $addedAssignees = $audienceResolver->usersByIds($event->condominiumId, $event->addedUserIds);
            $staff = $audienceResolver->usersForMaintenanceStaff($event->condominiumId);

            $users = $addedAssignees
                ->merge($staff)
                ->reject(fn ($user) => $event->actorId !== null && (int) $user->id === (int) $event->actorId)
                ->unique('id');

            if ($users->isEmpty()) {
                return;
            }

            $title = $schedule->title;
            $equipmentName = $schedule->equipment?->name;
            if ($equipmentName !== null && $equipmentName !== '') {
                $title = "{$schedule->title} ({$equipmentName})";
            }

            $startDate = $schedule->start_date?->format('d/m/Y') ?? '';
            $endDate = $schedule->end_date?->format('d/m/Y');
            $scheduleDates = $endDate !== null && $endDate !== ''
                ? "{$startDate} — {$endDate}"
                : $startDate;

            $addedUserIdSet = array_flip($event->addedUserIds);

            foreach ($users as $user) {
                $dedupeKey = isset($addedUserIdSet[$user->id])
                    ? "maintenance_schedule.assigned:{$schedule->id}:user:{$user->id}"
                    : "maintenance_schedule.assigned:{$schedule->id}:staff:{$user->id}";

                $dedupeGuard->notifyIfNotDuplicate($user, new MaintenanceScheduleAssignedNotification(
                    condominiumId: $event->condominiumId,
                    scheduleId: $schedule->id,
                    title: $title,
                    scheduleDates: $scheduleDates,
                    dedupeKey: $dedupeKey,
                    actorId: $event->actorId,
                ));
            }
        });
    }
}
