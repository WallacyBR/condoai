<?php

namespace App\Enums;

enum AccountType: string
{
    case RECEITA = 'receita';
    case DESPESA = 'despesa';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
