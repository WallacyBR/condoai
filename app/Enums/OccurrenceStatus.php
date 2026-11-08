<?php

namespace App\Enums;

enum OccurrenceStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
    case REOPENED = 'reopened';

    public static function labels(): array
    {
        return [
            'open' => 'Aberta',
            'in_progress' => 'Em Progresso',
            'resolved' => 'Resolvida',
            'closed' => 'Fechada',
            'reopened' => 'Reaberta',
        ];
    }

    public function getLabel(): string
    {
        return self::labels()[$this->value];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
