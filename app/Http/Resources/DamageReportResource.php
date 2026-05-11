<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DamageReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'severity' => $this->severity?->value,
            'status' => $this->status?->value,
            'reported_at' => $this->reported_at?->toJSON(),
            'resolved_at' => $this->resolved_at?->toJSON(),
            'asset' => $this->whenLoaded('asset', fn () => [
                'id' => $this->asset->id,
                'asset_code' => $this->asset->asset_code,
                'name' => $this->asset->name,
                'status' => $this->asset->status?->value,
                'category' => $this->asset->relationLoaded('category') && $this->asset->category ? [
                    'id' => $this->asset->category->id,
                    'code' => $this->asset->category->code,
                    'name' => $this->asset->category->name,
                ] : null,
            ]),
            'location' => $this->whenLoaded('location', fn () => $this->location ? [
                'id' => $this->location->id,
                'code' => $this->location->code,
                'name' => $this->location->name,
                'type' => $this->location->type,
                'floor_number' => $this->location->floor_number,
            ] : null),
            'reported_by_user' => $this->whenLoaded('reportedByUser', fn () => $this->reportedByUser ? [
                'id' => $this->reportedByUser->id,
                'name' => $this->reportedByUser->name,
                'email' => $this->reportedByUser->email,
                'role' => $this->reportedByUser->role?->value,
            ] : null),
            // Repair update CRUD is planned as a future manager follow-up workflow.
            'repair_updates' => $this->whenLoaded('repairUpdates', fn () => $this->repairUpdates->map(fn ($update) => [
                'id' => $update->id,
                'update_type' => $update->update_type?->value,
                'status_after' => $update->status_after?->value,
                'result_summary' => $update->result_summary,
                'notes' => $update->notes,
                'logged_at' => $update->logged_at?->toJSON(),
                'updated_by_user' => $update->relationLoaded('updatedByUser') && $update->updatedByUser ? [
                    'id' => $update->updatedByUser->id,
                    'name' => $update->updatedByUser->name,
                    'email' => $update->updatedByUser->email,
                    'role' => $update->updatedByUser->role?->value,
                ] : null,
            ])->values()),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
