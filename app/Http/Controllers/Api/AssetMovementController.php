<?php

namespace App\Http\Controllers\Api;

use App\Enums\MovementSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetMovementRequest;
use App\Http\Resources\AssetMovementResource;
use App\Models\Asset;
use App\Models\AssetMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssetMovementController extends Controller
{
    public function index(Request $request, Asset $asset): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'movement_source' => ['nullable', Rule::enum(MovementSource::class)],
            'from_location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')],
            'to_location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $movements = $asset->movements()
            ->with(['asset', 'fromLocation', 'toLocation', 'movedByUser'])
            ->when($validated['movement_source'] ?? null, fn ($query, $source) => $query->where('movement_source', $source))
            ->when(array_key_exists('from_location_id', $validated), fn ($query) => $query->where('from_location_id', $validated['from_location_id']))
            ->when($validated['to_location_id'] ?? null, fn ($query, $locationId) => $query->where('to_location_id', $locationId))
            ->when($validated['date_from'] ?? null, fn ($query, $date) => $query->where('moved_at', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($query, $date) => $query->where('moved_at', '<=', $date))
            ->newestFirst()
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        return AssetMovementResource::collection($movements);
    }

    public function store(StoreAssetMovementRequest $request, Asset $asset): JsonResponse
    {
        $validated = $request->validated();

        $movement = DB::transaction(function () use ($asset, $validated): AssetMovement {
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

        return AssetMovementResource::make(
            $movement->load(['asset', 'fromLocation', 'toLocation', 'movedByUser'])
        )
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(AssetMovement $assetMovement): AssetMovementResource
    {
        return AssetMovementResource::make(
            $assetMovement->load(['asset', 'fromLocation', 'toLocation', 'movedByUser'])
        );
    }
}
