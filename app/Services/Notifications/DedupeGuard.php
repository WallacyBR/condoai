<?php

namespace App\Services\Notifications;

use App\Notifications\CondoNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

/**
 * Evita notificações duplicadas por (notifiable, dedupe_key).
 */
class DedupeGuard
{
    public function exists(Model $notifiable, string $dedupeKey): bool
    {
        return DB::table('notifications')
            ->where('notifiable_type', $notifiable->getMorphClass())
            ->where('notifiable_id', $notifiable->getKey())
            ->where('data->dedupe_key', $dedupeKey)
            ->exists();
    }

    public function notifyIfNotDuplicate(Model $notifiable, CondoNotification $notification): bool
    {
        $payload = $notification->toArray($notifiable);
        $dedupeKey = $payload['dedupe_key'] ?? null;

        if ($dedupeKey !== null && $this->exists($notifiable, $dedupeKey)) {
            return false;
        }

        $notifiable->notify($notification);

        return true;
    }

    public function findByDedupeKey(Model $notifiable, string $dedupeKey): ?DatabaseNotification
    {
        /** @var DatabaseNotification|null $notification */
        $notification = $notifiable->notifications()
            ->where('data->dedupe_key', $dedupeKey)
            ->first();

        return $notification;
    }
}
