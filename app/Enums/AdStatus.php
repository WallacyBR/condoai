<?php

namespace App\Enums;

enum AdStatus: string
{
    case Active = 'Ativo';
    case Paused = 'Pausado';
    case Sold = 'Vendido';
    case Deleted = 'Excluído';
}
