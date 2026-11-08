<?php

namespace App\Listeners\Notifications;

use App\Events\CommonAreas\CommonAreaBookingWasCancelled;
use App\Models\CommonAreaBooking;
use App\Models\User;
use App\Notifications\CommonAreaBookingCancelledNotification;
use App\Services\Notifications\AudienceResolver;
use App\Services\Notifications\DedupeGuard;
use App\Support\RestoresTenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;

class SendCommonAreaBookingCancelledNotification implements ShouldQueue
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

    public function handle(CommonAreaBookingWasCancelled $event): void
    {
        $audienceResolver = app(AudienceResolver::class);
        $dedupeGuard = app(DedupeGuard::class);

        $this->withTenantContext($event->condominiumId, function () use ($event, $audienceResolver, $dedupeGuard): void {
            $booking = CommonAreaBooking::query()->with('commonArea')->findOrFail($event->bookingId);

            $users = $this->resolveCancelledAudience($event, $audienceResolver, $booking);

            if ($users->isEmpty()) {
                return;
            }

            $areaName = $booking->commonArea?->name ?? 'Área comum';
            $bookingDateTime = $booking->start_time?->format('d/m/Y H:i') ?? '';

            foreach ($users as $user) {
                $dedupeGuard->notifyIfNotDuplicate($user, new CommonAreaBookingCancelledNotification(
                    condominiumId: $event->condominiumId,
                    bookingId: $booking->id,
                    areaName: $areaName,
                    bookingDateTime: $bookingDateTime,
                    reason: $event->reason,
                    rejected: $event->rejected,
                    actorId: $event->actorId,
                ));
            }
        });
    }

    /**
     * @return Collection<int, User>
     */
    private function resolveCancelledAudience(
        CommonAreaBookingWasCancelled $event,
        AudienceResolver $audienceResolver,
        CommonAreaBooking $booking,
    ): Collection {
        $solicitante = $audienceResolver->usersForResident($event->condominiumId, $booking->resident_id);
        $staff = $audienceResolver->usersForBookingStaff($event->condominiumId);

        $actorIsStaff = false;
        $actorIsSolicitante = false;

        if ($event->actorId !== null) {
            $actor = User::query()->find($event->actorId);
            $actorIsStaff = $actor !== null && $actor->canModule('Áreas Comuns', 'view');
            $actorIsSolicitante = $solicitante->contains(fn ($user) => (int) $user->id === (int) $event->actorId);
        }

        $users = collect();

        if ($actorIsStaff) {
            $users = $users->merge($solicitante);
        } elseif ($actorIsSolicitante) {
            $users = $users->merge($staff);
        }

        return $users
            ->reject(fn ($user) => $event->actorId !== null && (int) $user->id === (int) $event->actorId)
            ->unique('id')
            ->values();
    }
}
