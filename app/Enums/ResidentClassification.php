<?php

namespace App\Enums;

enum ResidentClassification: string
{
    case PROPRIETARIO = 'proprietario';
    case MORADOR = 'morador';
    case INQUILINO = 'inquilino';

    public function label(): string
    {
        return match($this) {
            self::PROPRIETARIO => 'Proprietário',
            self::MORADOR => 'Morador',
            self::INQUILINO => 'Inquilino',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PROPRIETARIO => 'Proprietário do imóvel (pode ser morador ou não)',
            self::MORADOR => 'Pessoa que reside no imóvel',
            self::INQUILINO => 'Pessoa que aluga o imóvel',
        };
    }

    public static function options(): array
    {
        return [
            self::PROPRIETARIO->value => self::PROPRIETARIO->label(),
            self::MORADOR->value => self::MORADOR->label(),
            self::INQUILINO->value => self::INQUILINO->label(),
        ];
    }
} 