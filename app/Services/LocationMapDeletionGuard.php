<?php

namespace App\Services;

use App\Models\LocationMap;

class LocationMapDeletionGuard
{
    public const BLOCKED_MESSAGE = 'This item cannot be deleted because related records still exist.';

    public function isBlocked(LocationMap $locationMap): bool
    {
        return $locationMap->assets()->exists();
    }
}
