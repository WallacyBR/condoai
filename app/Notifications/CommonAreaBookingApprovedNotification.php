<?php

namespace App\Notifications;

use App\Models\CommonAreaBooking;

class CommonAreaBookingApprovedNotification extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly int $bookingId,
        private readonly string $areaName,
        private readonly string $bookingDateTime,
        private readonly ?int $actorId,
    ) {}

    protected function buildPayload(): array
    {
        return $this->basePayload(
            $this->condominiumId,
            'bookings',
            'Reserva aprovada',
            "{$this->areaName} — {$this->bookingDateTime}",
            "/bookings/{$this->bookingId}",
            "common_area_booking.approved:{$this->bookingId}",
            $this->actorId,
            CommonAreaBooking::class,
            $this->bookingId,
        );
    }
}
