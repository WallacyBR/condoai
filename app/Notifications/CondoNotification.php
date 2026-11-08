<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Base para notificações in-app (canal database) com payload canônico ADR-002 E1.
 *
 * Q2: via() retorna sempre ['database']; prefs notification_* não afetam o inbox.
 */
abstract class CondoNotification extends Notification
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->buildPayload();
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function buildPayload(): array;

    /**
     * Monta o payload obrigatório do inbox tenant-scoped.
     *
     * @return array<string, mixed>
     */
    protected function basePayload(
        int $condominiumId,
        string $category,
        string $title,
        string $body,
        string $actionUrl,
        string $dedupeKey,
        ?int $actorId = null,
        ?string $entityType = null,
        ?int $entityId = null,
    ): array {
        $payload = [
            'condominium_id' => $condominiumId,
            'category' => $category,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'dedupe_key' => $dedupeKey,
            'actor_id' => $actorId,
        ];

        if ($entityType !== null) {
            $payload['entity_type'] = $entityType;
        }

        if ($entityId !== null) {
            $payload['entity_id'] = $entityId;
        }

        return $payload;
    }
}
