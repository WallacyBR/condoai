<?php

namespace App\Notifications;

use App\Models\CommonAreaBooking;

class CommonAreaBookingCreatedNotification extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly int $bookingId,
        private readonly string $areaName,
        private readonly string $bookingDateTime,
        private readonly string $status,
        private readonly ?int $actorId,
        private readonly int $commonAreaId,
        private readonly int $apartmentId,
        private readonly ?string $bookingDate,
    ) {}

    protected function buildPayload(): array
    {
        $payload = array_merge(
            $this->basePayload(
                $this->condominiumId,
                'bookings',
                'Reserva criada',
                "{$this->areaName} — {$this->bookingDateTime} — {$this->status}",
                "/bookings/{$this->bookingId}",
                "common_area_booking.created:{$this->bookingId}",
                $this->actorId,
                CommonAreaBooking::class,
                $this->bookingId,
            ),
            [
                'common_area_id' => $this->commonAreaId,
                'apartment_id' => $this->apartmentId,
                'status' => $this->status,
            ],
        );

        if ($this->bookingDate !== null) {
            $payload['booking_date'] = $this->bookingDate;
        }

        return $payload;
    }
}
