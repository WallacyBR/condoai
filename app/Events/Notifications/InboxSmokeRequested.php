<?php

namespace App\Events\Notifications;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento de prova E2 — dispara listener queued sem emitters E3 de produto.
 */
class InboxSmokeRequested
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $condominiumId,
        public readonly ?int $actorId,
        public readonly int $userId,
    ) {}
}
