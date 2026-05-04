<?php

namespace App\Enums;

enum RepairUpdateType: string
{
    case Note = 'note';
    case Inspection = 'inspection';
    case Repair = 'repair';
}
