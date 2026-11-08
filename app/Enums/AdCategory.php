<?php

namespace App\Enums;

enum AdCategory: string
{
    case Electronics = 'Eletrônicos';
    case Furniture = 'Móveis';
    case Services = 'Serviços';
    case Vehicles = 'Veículos';
    case RealEstate = 'Imóveis';
    case Others = 'Outros';
}
