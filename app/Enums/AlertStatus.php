<?php

namespace App\Enums;

enum AlertStatus: string
{
    case New = 'new';
    case Acknowledged = 'acknowledged';
    case Resolved = 'resolved';
}
