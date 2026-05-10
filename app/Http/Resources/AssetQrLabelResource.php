<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetQrLabelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'qr_code_value' => $this->qr_code_value,
            'asset' => [
                'id' => $this->id,
                'asset_code' => $this->asset_code,
                'name' => $this->name,
                'status' => $this->status?->value,
                'brand' => $this->brand,
                'model' => $this->model,
                'serial_number' => $this->serial_number,
            ],
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'code' => $this->category->code,
                'name' => $this->category->name,
            ]),
            'current_location' => $this->whenLoaded('currentLocation', fn () => $this->currentLocation ? [
                'id' => $this->currentLocation->id,
                'code' => $this->currentLocation->code,
                'name' => $this->currentLocation->name,
                'type' => $this->currentLocation->type,
                'floor_number' => $this->currentLocation->floor_number,
            ] : null),
            'current_map' => $this->whenLoaded('currentMap', fn () => $this->currentMap ? [
                'id' => $this->currentMap->id,
                'name' => $this->currentMap->name,
                'image_path' => $this->currentMap->image_path,
                'image_width' => $this->currentMap->image_width,
                'image_height' => $this->currentMap->image_height,
            ] : null),
            'label_state' => [
                'has_qr_label' => $this->qr_code_value !== null,
                'has_map_placement' => $this->hasMapPlacement(),
                'has_printable_code' => $this->hasPrintableCode(),
            ],
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
