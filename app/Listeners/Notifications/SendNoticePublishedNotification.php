<?php

namespace App\Listeners\Notifications;

use App\Events\Notices\NoticeWasPublished;
use App\Models\Notice;
use App\Notifications\NoticePublishedNotification;
use App\Services\Notifications\AudienceResolver;
use App\Services\Notifications\DedupeGuard;
use App\Support\RestoresTenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNoticePublishedNotification implements ShouldQueue
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

    public function handle(NoticeWasPublished $event): void
    {
        $audienceResolver = app(AudienceResolver::class);
        $dedupeGuard = app(DedupeGuard::class);

        $this->withTenantContext($event->condominiumId, function () use ($event, $audienceResolver, $dedupeGuard): void {
            $notice = Notice::query()
                ->with(['apartments', 'blocks', 'towers'])
                ->findOrFail($event->noticeId);

            $users = $audienceResolver->usersForNotice($event->condominiumId, $notice);

            if ($users->isEmpty()) {
                return;
            }

            foreach ($users as $user) {
                $dedupeGuard->notifyIfNotDuplicate($user, new NoticePublishedNotification(
                    condominiumId: $event->condominiumId,
                    noticeId: $notice->id,
                    title: $notice->title,
                    body: $notice->body,
                    actorId: $event->actorId,
                ));
            }
        });
    }
}
