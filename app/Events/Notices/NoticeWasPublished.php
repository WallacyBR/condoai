<?php

namespace App\Events\Notices;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoticeWasPublished
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $condominiumId,
        public readonly ?int $actorId,
        public readonly int $noticeId,
    ) {}
}
