<?php

namespace App\Services;

use App\Enums\GeofenceRuleType;
use App\Models\Asset;
use App\Models\AssetGeofence;
use App\Models\Location;
use Illuminate\Support\Collection;

class AssetGeofenceRuleSyncService
{
    /**
     * @param  array{enabled: bool, notify_on_room_change: bool, forbidden_location_ids: array<int, int>}  $payload
     */
    public function sync(Asset $asset, array $payload): void
    {
        $existingGeofences = $asset->geofences()->get()->groupBy(fn (AssetGeofence $geofence) => $this->groupKey(
            $geofence->rule_type,
            $geofence->location_id,
        ));

        $desiredKeys = collect();

        if (! $payload['enabled']) {
            $asset->geofences()->delete();

            return;
        }

        if ($payload['notify_on_room_change']) {
            $desiredKeys->push($this->groupKey(GeofenceRuleType::RoomChangeNotification, null));

            AssetGeofence::query()->updateOrCreate(
                [
                    'asset_id' => $asset->id,
                    'rule_type' => GeofenceRuleType::RoomChangeNotification->value,
                    'location_id' => null,
                ],
                [
                    'name' => 'Ketika Pindah Ruangan',
                    'category_id' => null,
                    'is_active' => true,
                    'notes' => 'Buat notifikasi ketika aset berpindah ke ruangan lain.',
                ],
            );
        }

        $forbiddenLocations = Location::query()
            ->whereIn('id', $payload['forbidden_location_ids'])
            ->get(['id', 'name']);

        $forbiddenLocations->each(function (Location $location) use ($asset, $desiredKeys): void {
            $desiredKeys->push($this->groupKey(GeofenceRuleType::RestrictedEntry, $location->id));

            AssetGeofence::query()->updateOrCreate(
                [
                    'asset_id' => $asset->id,
                    'rule_type' => GeofenceRuleType::RestrictedEntry->value,
                    'location_id' => $location->id,
                ],
                [
                    'name' => 'Ruangan Terlarang: '.$location->name,
                    'category_id' => null,
                    'is_active' => true,
                    'notes' => 'Buat notifikasi ketika aset dipindahkan ke ruangan ini.',
                ],
            );
        });

        $obsoleteIds = $existingGeofences
            ->reject(fn (Collection $group, string $groupKey) => $desiredKeys->contains($groupKey))
            ->flatten()
            ->pluck('id');

        if ($obsoleteIds->isNotEmpty()) {
            AssetGeofence::query()->whereKey($obsoleteIds)->delete();
        }
    }

    private function groupKey(GeofenceRuleType $ruleType, ?int $locationId): string
    {
        return $ruleType->value.'|'.($locationId ?? 'none');
    }
}
