<?php

namespace App\Notifications;

use App\Models\Notice;

class NoticePublishedNotification extends CondoNotification
{
    private const BODY_TRUNCATE_LENGTH = 280;

    public function __construct(
        private readonly int $condominiumId,
        private readonly int $noticeId,
        private readonly string $title,
        private readonly string $body,
        private readonly ?int $actorId,
    ) {}

    protected function buildPayload(): array
    {
        $truncatedBody = mb_strlen($this->body) > self::BODY_TRUNCATE_LENGTH
            ? mb_substr($this->body, 0, self::BODY_TRUNCATE_LENGTH).'…'
            : $this->body;

        return $this->basePayload(
            $this->condominiumId,
            'notices',
            $this->title,
            $truncatedBody,
            "/notices/{$this->noticeId}",
            "notice.published:{$this->noticeId}",
            $this->actorId,
            Notice::class,
            $this->noticeId,
        );
    }
}
