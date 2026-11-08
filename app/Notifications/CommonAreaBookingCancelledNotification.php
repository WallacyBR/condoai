<?php

namespace App\Notifications;

use App\Models\CommonAreaBooking;

class CommonAreaBookingCancelledNotification extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly int $bookingId,
        private readonly string $areaName,
        private readonly string $bookingDateTime,
        private readonly ?string $reason,
        private readonly bool $rejected,
        private readonly ?int $actorId,
    ) {}

    protected function buildPayload(): array
    {
        $action = $this->rejected ? 'rejeitada' : 'cancelada';
        $body = "{$this->areaName} — {$this->bookingDateTime} — {$action}";
        if ($this->reason !== null && $this->reason !== '') {
            $body .= " — {$this->reason}";
        }

        return array_merge(
            $this->basePayload(
                $this->condominiumId,
                'bookings',
                'Reserva '.$action,
                $body,
                "/bookings/{$this->bookingId}",
                "common_area_booking.cancelled:{$this->bookingId}",
                $this->actorId,
                CommonAreaBooking::class,
                $this->bookingId,
            ),
            [
                'rejected' => $this->rejected,
                'cancellation_reason' => $this->reason,
            ],
        );
    }
}
