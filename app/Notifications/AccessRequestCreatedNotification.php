<?php

namespace App\Notifications;

use App\Models\AccessRequest;

class AccessRequestCreatedNotification extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly int $accessRequestId,
        private readonly string $visitorName,
        private readonly string $apartmentLabel,
        private readonly string $visitDate,
        private readonly ?int $actorId,
        private readonly int $apartmentId,
    ) {}

    protected function buildPayload(): array
    {
        return array_merge(
            $this->basePayload(
                $this->condominiumId,
                'access',
                'Nova solicitação de acesso',
                "{$this->visitorName} — unidade {$this->apartmentLabel} — {$this->visitDate}",
                "/access-control/requests/{$this->accessRequestId}",
                "access_request.created:{$this->accessRequestId}",
                $this->actorId,
                AccessRequest::class,
                $this->accessRequestId,
            ),
            [
                'apartment_id' => $this->apartmentId,
                'status' => 'pending',
                'visitor_name' => $this->visitorName,
            ],
        );
    }
}
