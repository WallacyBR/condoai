<?php

namespace App\Notifications;

use App\Models\AccessRequest;

class AccessRequestRespondedNotification extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly int $accessRequestId,
        private readonly string $status,
        private readonly string $visitorName,
        private readonly string $apartmentLabel,
        private readonly ?int $actorId,
        private readonly int $apartmentId,
    ) {}

    protected function buildPayload(): array
    {
        $title = $this->status === 'authorized' ? 'Acesso autorizado' : 'Acesso negado';

        return array_merge(
            $this->basePayload(
                $this->condominiumId,
                'access',
                $title,
                "{$this->visitorName} — unidade {$this->apartmentLabel}",
                "/access-control/requests/{$this->accessRequestId}",
                "access_request.responded:{$this->accessRequestId}:{$this->status}",
                $this->actorId,
                AccessRequest::class,
                $this->accessRequestId,
            ),
            [
                'status' => $this->status,
                'apartment_id' => $this->apartmentId,
            ],
        );
    }
}
