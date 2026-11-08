<?php

namespace App\Listeners\Notifications;

use App\Events\CommonAreas\CommonAreaBookingWasCreated;
use App\Models\CommonAreaBooking;
use App\Notifications\CommonAreaBookingCreatedNotification;
use App\Services\Notifications\AudienceResolver;
use App\Services\Notifications\DedupeGuard;
use App\Support\RestoresTenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCommonAreaBookingCreatedNotification implements ShouldQueue
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

    public function handle(CommonAreaBookingWasCreated $event): void
    {
        $audienceResolver = app(AudienceResolver::class);
        $dedupeGuard = app(DedupeGuard::class);

        $this->withTenantContext($event->condominiumId, function () use ($event, $audienceResolver, $dedupeGuard): void {
            $booking = CommonAreaBooking::query()
                ->with(['commonArea', 'apartment'])
                ->findOrFail($event->bookingId);

            $solicitante = $audienceResolver->usersForResident($event->condominiumId, $booking->resident_id);

            if ($booking->status === CommonAreaBooking::STATUS_CONFIRMED) {
                $users = $solicitante
                    ->reject(fn ($user) => $event->actorId !== null && (int) $user->id === (int) $event->actorId)
                    ->unique('id');
            } else {
                $users = $solicitante
                    ->merge($audienceResolver->usersForBookingStaff($event->condominiumId))
                    ->reject(fn ($user) => $event->actorId !== null && (int) $user->id === (int) $event->actorId)
                    ->unique('id');
            }

            if ($users->isEmpty()) {
                return;
            }

            $areaName = $booking->commonArea?->name ?? 'Área comum';
            $bookingDateTime = $booking->start_time?->format('d/m/Y H:i') ?? '';
            $bookingDate = $booking->booking_date?->format('Y-m-d');

            foreach ($users as $user) {
                $dedupeGuard->notifyIfNotDuplicate($user, new CommonAreaBookingCreatedNotification(
                    condominiumId: $event->condominiumId,
                    bookingId: $booking->id,
                    areaName: $areaName,
                    bookingDateTime: $bookingDateTime,
                    status: $booking->status,
                    actorId: $event->actorId,
                    commonAreaId: $booking->common_area_id,
                    apartmentId: $booking->apartment_id,
                    bookingDate: $bookingDate,
                ));
            }
        });
    }
}
