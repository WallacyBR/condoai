<?php

namespace App\Listeners\Notifications;

use App\Events\Packages\PackageWasReceived;
use App\Models\Package;
use App\Notifications\PackageReceivedNotification;
use App\Services\Notifications\AudienceResolver;
use App\Services\Notifications\DedupeGuard;
use App\Support\RestoresTenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPackageReceivedNotification implements ShouldQueue
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

    public function handle(PackageWasReceived $event): void
    {
        $audienceResolver = app(AudienceResolver::class);
        $dedupeGuard = app(DedupeGuard::class);

        $this->withTenantContext($event->condominiumId, function () use ($event, $audienceResolver, $dedupeGuard): void {
            $package = Package::query()->findOrFail($event->packageId);

            $users = $audienceResolver
                ->usersForApartmentPackageAudience($event->condominiumId, $event->apartmentId)
                ->merge($audienceResolver->usersWithModule($event->condominiumId, 'Correspondências', 'view'))
                ->unique('id');

            if ($users->isEmpty()) {
                return;
            }

            foreach ($users as $user) {
                $dedupeGuard->notifyIfNotDuplicate($user, new PackageReceivedNotification(
                    condominiumId: $event->condominiumId,
                    packageId: $package->id,
                    apartmentId: $event->apartmentId,
                    recipientApartment: $package->recipient_apartment ?? '',
                    code: $package->code,
                    actorId: $event->actorId,
                ));
            }
        });
    }
}
