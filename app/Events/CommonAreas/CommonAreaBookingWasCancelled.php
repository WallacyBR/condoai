<?php

namespace App\Events\CommonAreas;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommonAreaBookingWasCancelled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $condominiumId,
        public readonly ?int $actorId,
        public readonly int $bookingId,
        public readonly ?string $reason,
        public readonly bool $rejected,
    ) {}
}
