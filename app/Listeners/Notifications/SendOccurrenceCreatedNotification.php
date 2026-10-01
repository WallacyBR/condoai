<?php

namespace App\Listeners\Notifications;

use App\Events\Occurrences\OccurrenceWasCreated;
use App\Models\Occurrence;
use App\Notifications\OccurrenceCreatedNotification;
use App\Services\Notifications\AudienceResolver;
use App\Services\Notifications\DedupeGuard;
use App\Support\RestoresTenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOccurrenceCreatedNotification implements ShouldQueue
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

    public function handle(OccurrenceWasCreated $event): void
    {
        $audienceResolver = app(AudienceResolver::class);
        $dedupeGuard = app(DedupeGuard::class);

        $this->withTenantContext($event->condominiumId, function () use ($event, $audienceResolver, $dedupeGuard): void {
            $occurrence = Occurrence::query()->findOrFail($event->occurrenceId);

            $users = $audienceResolver
                ->usersWithModule($event->condominiumId, 'Ocorrências', 'view')
                ->reject(fn ($user) => $event->actorId !== null && (int) $user->id === (int) $event->actorId)
                ->unique('id');

            if ($users->isEmpty()) {
                return;
            }

            $priority = $occurrence->priority instanceof \BackedEnum
                ? $occurrence->priority->value
                : (string) $occurrence->priority;
            $status = $occurrence->status instanceof \BackedEnum
                ? $occurrence->status->value
                : (string) $occurrence->status;

            foreach ($users as $user) {
                $dedupeGuard->notifyIfNotDuplicate($user, new OccurrenceCreatedNotification(
                    condominiumId: $event->condominiumId,
                    occurrenceId: $occurrence->id,
                    title: $occurrence->title,
                    priority: $priority,
                    status: $status,
                    actorId: $event->actorId,
                ));
            }
        });
    }
}
