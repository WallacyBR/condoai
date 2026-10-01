<?php

namespace App\Enums;

enum PersonType: string
{
    case FISICA = 'fisica';
    case JURIDICA = 'juridica';

    public function label(): string
    {
        return match ($this) {
            self::FISICA => 'Pessoa Física',
            self::JURIDICA => 'Pessoa Jurídica',
        };
    }

    public static function options(): array
    {
        return [
            self::FISICA->value => self::FISICA->label(),
            self::JURIDICA->value => self::JURIDICA->label(),
        ];
    }
}
