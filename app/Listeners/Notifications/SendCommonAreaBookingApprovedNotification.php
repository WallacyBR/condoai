<?php

namespace App\Listeners\Notifications;

use App\Events\CommonAreas\CommonAreaBookingWasApproved;
use App\Models\CommonAreaBooking;
use App\Notifications\CommonAreaBookingApprovedNotification;
use App\Services\Notifications\AudienceResolver;
use App\Services\Notifications\DedupeGuard;
use App\Support\RestoresTenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCommonAreaBookingApprovedNotification implements ShouldQueue
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

    public function handle(CommonAreaBookingWasApproved $event): void
    {
        $audienceResolver = app(AudienceResolver::class);
        $dedupeGuard = app(DedupeGuard::class);

        $this->withTenantContext($event->condominiumId, function () use ($event, $audienceResolver, $dedupeGuard): void {
            $booking = CommonAreaBooking::query()->with('commonArea')->findOrFail($event->bookingId);

            $users = $audienceResolver
                ->usersForResident($event->condominiumId, $booking->resident_id)
                ->reject(fn ($user) => $event->actorId !== null && (int) $user->id === (int) $event->actorId)
                ->unique('id');

            if ($users->isEmpty()) {
                return;
            }

            $areaName = $booking->commonArea?->name ?? 'Área comum';
            $bookingDateTime = $booking->start_time?->format('d/m/Y H:i') ?? '';

            foreach ($users as $user) {
                $dedupeGuard->notifyIfNotDuplicate($user, new CommonAreaBookingApprovedNotification(
                    condominiumId: $event->condominiumId,
                    bookingId: $booking->id,
                    areaName: $areaName,
                    bookingDateTime: $bookingDateTime,
                    actorId: $event->actorId,
                ));
            }
        });
    }
}
