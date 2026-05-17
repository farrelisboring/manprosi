<?php

namespace App\Http\Controllers\Api;

use App\Enums\MovementSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetMovementRequest;
use App\Http\Resources\AssetMovementResource;
use App\Models\Asset;
use App\Models\AssetMovement;
use App\Services\AssetMovementRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
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
            ->withFilters($validated)
            ->newestFirst()
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        return AssetMovementResource::collection($movements);
    }

    public function store(StoreAssetMovementRequest $request, Asset $asset, AssetMovementRecorder $recorder): JsonResponse
    {
        $movement = $recorder->record($asset, $request->validated());

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
