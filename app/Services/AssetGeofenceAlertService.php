<?php

namespace App\Services;

use App\Enums\AlertStatus;
use App\Enums\AlertType;
use App\Enums\GeofenceRuleType;
use App\Models\Asset;
use App\Models\AssetAlert;
use App\Models\AssetGeofence;
use App\Models\AssetMovement;

class AssetGeofenceAlertService
{
    public function createAlertsForMovement(Asset $asset, AssetMovement $movement): void
    {
        $geofences = $asset->geofences()
            ->active()
            ->with('location:id,name')
            ->get();

        if ($geofences->isEmpty()) {
            return;
        }

        $hasRoomChange = $movement->from_location_id !== null
            && $movement->from_location_id !== $movement->to_location_id;

        $enteredDifferentRoom = $movement->from_location_id !== $movement->to_location_id;

        $roomChangeGeofence = $hasRoomChange
            ? $geofences->firstWhere('rule_type', GeofenceRuleType::RoomChangeNotification)
            : null;

        $forbiddenGeofence = $enteredDifferentRoom
            ? $geofences->first(fn (AssetGeofence $geofence) => $geofence->rule_type === GeofenceRuleType::RestrictedEntry
                && $geofence->location_id === $movement->to_location_id)
            : null;

        if (! $roomChangeGeofence && ! $forbiddenGeofence) {
            return;
        }

        AssetAlert::query()->create([
            'asset_id' => $asset->id,
            'geofence_id' => $forbiddenGeofence?->id ?? $roomChangeGeofence?->id,
            'location_id' => $movement->to_location_id,
            'alert_type' => AlertType::GeofenceBreach->value,
            'message' => $this->buildMessage($forbiddenGeofence, $roomChangeGeofence),
            'status' => AlertStatus::New->value,
            'triggered_at' => $movement->moved_at,
        ]);
    }

    private function buildMessage(?AssetGeofence $forbiddenGeofence, ?AssetGeofence $roomChangeGeofence): string
    {
        if ($forbiddenGeofence && $roomChangeGeofence) {
            return 'Aset berpindah ke ruangan lain dan masuk ke ruangan terlarang: '.($forbiddenGeofence->location?->name ?? 'Unknown room').'.';
        }

        if ($forbiddenGeofence) {
            return 'Aset dipindahkan ke ruangan terlarang: '.($forbiddenGeofence->location?->name ?? 'Unknown room').'.';
        }

        return 'Aset berpindah ke ruangan lain.';
    }
}
