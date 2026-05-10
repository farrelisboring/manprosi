<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AssetController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $assets = Asset::query()
            ->with(['category', 'currentLocation', 'currentMap'])
            ->search($request->string('search')->toString())
            ->when($request->filled('category_id'), fn ($query) => $query->forCategory((int) $request->integer('category_id')))
            ->when($request->filled('current_location_id'), fn ($query) => $query->atLocation((int) $request->integer('current_location_id')))
            ->when($request->filled('status'), fn ($query) => $query->withStatus($request->string('status')->toString()))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return AssetResource::collection($assets);
    }

    public function store(StoreAssetRequest $request): AssetResource
    {
        $asset = Asset::create($request->validated());

        return AssetResource::make(
            $asset->refresh()->load(['category', 'currentLocation', 'currentMap'])
        );
    }

    public function show(Asset $asset): AssetResource
    {
        return AssetResource::make(
            $asset->load(['category', 'currentLocation', 'currentMap'])
        );
    }

    public function update(UpdateAssetRequest $request, Asset $asset): AssetResource
    {
        $asset->update($request->validated());

        return AssetResource::make(
            $asset->refresh()->load(['category', 'currentLocation', 'currentMap'])
        );
    }

    public function destroy(Asset $asset): Response
    {
        $asset->delete();

        return response()->noContent();
    }
}
