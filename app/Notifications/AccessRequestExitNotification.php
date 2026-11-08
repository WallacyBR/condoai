<?php

namespace App\Notifications;

use App\Models\AccessRequest;

class AccessRequestExitNotification extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly int $accessRequestId,
        private readonly string $visitorName,
        private readonly string $apartmentLabel,
        private readonly ?int $actorId,
    ) {}

    protected function buildPayload(): array
    {
        return $this->basePayload(
            $this->condominiumId,
            'access',
            'Saída registrada',
            "{$this->visitorName} — unidade {$this->apartmentLabel}",
            "/access-control/requests/{$this->accessRequestId}",
            "access_request.exit:{$this->accessRequestId}",
            $this->actorId,
            AccessRequest::class,
            $this->accessRequestId,
        );
    }
}
