<?php

namespace App\Events\Access;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccessRequestWasResponded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $condominiumId,
        public readonly ?int $actorId,
        public readonly int $accessRequestId,
        public readonly string $status,
    ) {}
}
