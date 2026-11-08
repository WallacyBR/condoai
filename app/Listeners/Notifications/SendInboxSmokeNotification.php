<?php

namespace App\Listeners\Notifications;

use App\Events\Notifications\InboxSmokeRequested;
use App\Notifications\SmokeTestNotification;
use App\Services\Notifications\AudienceResolver;
use App\Services\Notifications\DedupeGuard;
use App\Support\RestoresTenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use RuntimeException;

/**
 * Listener queued de prova E2: Event → ShouldQueue → DedupeGuard → SmokeTestNotification.
 *
 * A Notification não implementa ShouldQueue (um hop apenas).
 */
class SendInboxSmokeNotification implements ShouldQueue
{
    use InteractsWithQueue;
    use RestoresTenantContext;

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(InboxSmokeRequested $event): void
    {
        $audienceResolver = app(AudienceResolver::class);
        $dedupeGuard = app(DedupeGuard::class);

        $this->withTenantContext($event->condominiumId, function () use ($event, $audienceResolver, $dedupeGuard): void {
            $users = $audienceResolver->singleUser($event->condominiumId, $event->userId);

            if ($users->isEmpty()) {
                throw new RuntimeException(
                    "Usuário {$event->userId} não pertence ao condomínio {$event->condominiumId}."
                );
            }

            $dedupeKey = "inbox-smoke-{$event->condominiumId}-{$event->userId}";

            foreach ($users as $user) {
                $dedupeGuard->notifyIfNotDuplicate($user, new SmokeTestNotification(
                    condominiumId: $event->condominiumId,
                    category: 'system',
                    title: 'Smoke E2',
                    body: 'Notificação de prova via fila (Event → Listener).',
                    actionUrl: '/dashboard',
                    dedupeKey: $dedupeKey,
                    actorId: $event->actorId,
                ));
            }
        });
    }
}
