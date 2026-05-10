<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_code' => $this->asset_code,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'brand' => $this->brand,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'barcode_value' => $this->barcode_value,
            'qr_code_value' => $this->qr_code_value,
            'rfid_tag' => $this->rfid_tag,
            'status' => $this->status?->value,
            'current_location_id' => $this->current_location_id,
            'current_map_id' => $this->current_map_id,
            'position_x' => $this->position_x,
            'position_y' => $this->position_y,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
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
                'location_id' => $this->currentMap->location_id,
                'name' => $this->currentMap->name,
                'image_path' => $this->currentMap->image_path,
                'image_width' => $this->currentMap->image_width,
                'image_height' => $this->currentMap->image_height,
            ] : null),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
