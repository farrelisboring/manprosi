<?php

namespace App\Enums;

enum DamageStatus: string
{
    case Reported = 'reported';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
}
