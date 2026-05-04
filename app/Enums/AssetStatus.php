<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Available = 'available';
    case InUse = 'in_use';
    case Maintenance = 'maintenance';
}
