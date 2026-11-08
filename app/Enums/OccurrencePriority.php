<?php

namespace App\Enums;

enum OccurrencePriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public static function labels(): array
    {
        return [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'urgent' => 'Urgente',
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
