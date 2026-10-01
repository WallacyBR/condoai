<?php

namespace App\Events\Packages;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageWasReceived
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $condominiumId,
        public readonly ?int $actorId,
        public readonly int $packageId,
        public readonly int $apartmentId,
    ) {}
}
