<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movement_source' => $this->movement_source?->value,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'moved_at' => $this->moved_at?->toJSON(),
            'asset' => $this->whenLoaded('asset', fn () => [
                'id' => $this->asset->id,
                'asset_code' => $this->asset->asset_code,
                'name' => $this->asset->name,
                'status' => $this->asset->status?->value,
            ]),
            'from_location' => $this->whenLoaded('fromLocation', fn () => $this->fromLocation ? [
                'id' => $this->fromLocation->id,
                'code' => $this->fromLocation->code,
                'name' => $this->fromLocation->name,
                'type' => $this->fromLocation->type,
                'floor_number' => $this->fromLocation->floor_number,
            ] : null),
            'to_location' => $this->whenLoaded('toLocation', fn () => [
                'id' => $this->toLocation->id,
                'code' => $this->toLocation->code,
                'name' => $this->toLocation->name,
                'type' => $this->toLocation->type,
                'floor_number' => $this->toLocation->floor_number,
            ]),
            'moved_by_user' => $this->whenLoaded('movedByUser', fn () => $this->movedByUser ? [
                'id' => $this->movedByUser->id,
                'name' => $this->movedByUser->name,
                'email' => $this->movedByUser->email,
                'role' => $this->movedByUser->role?->value,
            ] : null),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
