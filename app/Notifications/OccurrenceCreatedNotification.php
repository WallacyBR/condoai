<?php

namespace App\Notifications;

use App\Models\Occurrence;

class OccurrenceCreatedNotification extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly int $occurrenceId,
        private readonly string $title,
        private readonly string $priority,
        private readonly string $status,
        private readonly ?int $actorId,
    ) {}

    protected function buildPayload(): array
    {
        return array_merge(
            $this->basePayload(
                $this->condominiumId,
                'occurrences',
                $this->title,
                "Prioridade: {$this->priority}",
                "/occurrences/{$this->occurrenceId}",
                "occurrence.created:{$this->occurrenceId}",
                $this->actorId,
                Occurrence::class,
                $this->occurrenceId,
            ),
            [
                'priority' => $this->priority,
                'status' => $this->status,
            ],
        );
    }
}
