<?php

namespace App\Listeners\Notifications;

use App\Events\Access\AccessRequestWasCreated;
use App\Models\AccessRequest;
use App\Notifications\AccessRequestCreatedNotification;
use App\Services\Notifications\AudienceResolver;
use App\Services\Notifications\DedupeGuard;
use App\Support\RestoresTenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAccessRequestCreatedNotification implements ShouldQueue
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

    public function handle(AccessRequestWasCreated $event): void
    {
        $audienceResolver = app(AudienceResolver::class);
        $dedupeGuard = app(DedupeGuard::class);

        $this->withTenantContext($event->condominiumId, function () use ($event, $audienceResolver, $dedupeGuard): void {
            $accessRequest = AccessRequest::query()->with('apartment')->findOrFail($event->accessRequestId);

            $users = $audienceResolver
                ->usersForApartment($event->condominiumId, $accessRequest->apartment_id)
                ->reject(fn ($user) => $event->actorId !== null && (int) $user->id === (int) $event->actorId)
                ->unique('id');

            if ($users->isEmpty()) {
                return;
            }

            $apartmentLabel = $accessRequest->apartment?->number ?? (string) $accessRequest->apartment_id;
            $visitDate = $accessRequest->visit_date?->format('d/m/Y') ?? now()->format('d/m/Y');

            foreach ($users as $user) {
                $dedupeGuard->notifyIfNotDuplicate($user, new AccessRequestCreatedNotification(
                    condominiumId: $event->condominiumId,
                    accessRequestId: $accessRequest->id,
                    visitorName: $accessRequest->visitor_name,
                    apartmentLabel: $apartmentLabel,
                    visitDate: $visitDate,
                    actorId: $event->actorId,
                    apartmentId: $accessRequest->apartment_id,
                ));
            }
        });
    }
}
