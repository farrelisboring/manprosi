<?php

namespace App\Services;

use App\Enums\MovementSource;
use App\Models\Asset;
use App\Models\AssetMovement;
use Illuminate\Support\Facades\DB;

class AssetMovementRecorder
{
    public function record(Asset $asset, array $validated): AssetMovement
    {
        return DB::transaction(function () use ($asset, $validated): AssetMovement {
            $movement = AssetMovement::create([
                'asset_id' => $asset->id,
                'from_location_id' => $asset->current_location_id,
                'to_location_id' => $validated['to_location_id'],
                'moved_by_user_id' => $validated['moved_by_user_id'] ?? null,
                'movement_source' => $validated['movement_source'] ?? MovementSource::Manual->value,
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'moved_at' => $validated['moved_at'] ?? now(),
            ]);

            $asset->forceFill([
                'current_location_id' => $validated['to_location_id'],
                'current_map_id' => $validated['current_map_id'] ?? null,
                'position_x' => $validated['position_x'] ?? null,
                'position_y' => $validated['position_y'] ?? null,
            ])->save();

            return $movement;
        });
    }
}
