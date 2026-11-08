<?php

namespace App\Enums;

enum AdInteractionType: string
{
    case View = 'View';
    case ContactClick = 'Contact_Click';
    case Report = 'Report';
    case MarkSold = 'Mark_Sold';
    case MarkUnsold = 'Mark_Unsold';
}
