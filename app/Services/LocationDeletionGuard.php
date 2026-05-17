<?php

namespace App\Services;

use App\Models\Location;

class LocationDeletionGuard
{
    public const BLOCKED_MESSAGE = 'This item cannot be deleted because related records still exist.';

    public function isBlocked(Location $location): bool
    {
        return $location->currentAssets()->exists()
            || $location->incomingMovements()->exists()
            || $location->outgoingMovements()->exists()
            || $location->trackingEvents()->exists()
            || $location->geofences()->exists()
            || $location->alerts()->exists()
            || $location->damageReports()->exists();
    }
}
