<?php

namespace App\Notifications;

/**
 * Notificação concreta para testes/smoke de E1 (sem emitters E3).
 */
class SmokeTestNotification extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly string $category,
        private readonly string $title,
        private readonly string $body,
        private readonly string $actionUrl,
        private readonly string $dedupeKey,
        private readonly ?int $actorId = null,
        private readonly ?string $entityType = null,
        private readonly ?int $entityId = null,
    ) {}

    protected function buildPayload(): array
    {
        return $this->basePayload(
            $this->condominiumId,
            $this->category,
            $this->title,
            $this->body,
            $this->actionUrl,
            $this->dedupeKey,
            $this->actorId,
            $this->entityType,
            $this->entityId,
        );
    }
}
