<?php

namespace App\Enums;

enum AdCondition: string
{
    case New = 'Novo';
    case LikeNew = 'Usado - Como Novo';
    case Good = 'Usado - Bom Estado';
    case Fair = 'Usado - Com Marcas';
}
