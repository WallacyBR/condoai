<?php

namespace App\Events\Occurrences;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OccurrenceWasCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $condominiumId,
        public readonly ?int $actorId,
        public readonly int $occurrenceId,
    ) {}
}
