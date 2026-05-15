<?php

namespace App\Http\Requests\Concerns;

use App\Models\LocationMap;
use Illuminate\Validation\Validator;

trait ValidatesAssetPlacement
{
    protected function normalizeAssetPlacementInput(): void
    {
        if (! $this->filled('current_map_id')) {
            $this->merge([
                'current_map_id' => null,
                'position_x' => null,
                'position_y' => null,
            ]);
        }
    }

    protected function assetPlacementRules(): array
    {
        return [
            'current_map_id' => ['nullable', 'integer', 'exists:location_maps,id'],
            'position_x' => ['nullable', 'numeric', 'between:-9999.9999,9999.9999', 'required_with:position_y'],
            'position_y' => ['nullable', 'numeric', 'between:-9999.9999,9999.9999', 'required_with:position_x'],
        ];
    }

    protected function assetPlacementAfter(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->filled('current_map_id')) {
                    return;
                }

                if (! $this->filled('current_location_id')) {
                    $validator->errors()->add('current_location_id', 'Select a current location before choosing a map.');

                    return;
                }

                $mapBelongsToLocation = LocationMap::query()
                    ->whereKey($this->integer('current_map_id'))
                    ->where('location_id', $this->integer('current_location_id'))
                    ->exists();

                if (! $mapBelongsToLocation) {
                    $validator->errors()->add('current_map_id', 'The selected map must belong to the current location.');
                }
            },
        ];
    }
}
