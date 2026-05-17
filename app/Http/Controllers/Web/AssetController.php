<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\LocationMap;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['category_id', 'current_location_id', 'status']);

        $assets = Asset::query()
            ->with(['category', 'currentLocation', 'currentMap'])
            ->withFilters($filters)
            ->latest()
            ->paginate(15)
            ->appends($filters);

        return view('assets.index', [
            'assets' => $assets,
            'categories' => $this->categories(),
            'locations' => $this->locations(),
            'statusOptions' => AssetStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('assets.create', $this->formViewData($request));
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $asset = Asset::create($request->validated());

        return redirect()
            ->route('web.assets.show', $asset)
            ->with('status_message', 'Asset created successfully.')
            ->with('status_type', 'success');
    }

    public function show(Asset $asset): View
    {
        return view('assets.show', [
            'asset' => $asset->load(['category', 'currentLocation', 'currentMap']),
        ]);
    }

    public function edit(Request $request, Asset $asset): View
    {
        return view('assets.edit', $this->formViewData($request, $asset));
    }

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $asset->update($request->validated());

        return redirect()
            ->route('web.assets.show', $asset)
            ->with('status_message', 'Asset updated successfully.')
            ->with('status_type', 'success');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $asset->delete();

        return redirect()
            ->route('web.assets.index')
            ->with('status_message', 'Asset deleted successfully.')
            ->with('status_type', 'success');
    }

    private function formViewData(Request $request, ?Asset $asset = null): array
    {
        $categories = $this->categories();
        $selectedLocationId = $this->selectedLocationId($asset);

        return [
            'asset' => $asset?->loadMissing(['category', 'currentLocation', 'currentMap']),
            'categories' => $categories,
            'locations' => $this->locations(),
            'maps' => $this->mapsForLocation($selectedLocationId),
            'allMaps' => $this->allMaps(),
            'selectedLocationId' => $selectedLocationId,
            'statusOptions' => AssetStatus::cases(),
            'blockedByMissingCategories' => $categories->isEmpty(),
        ];
    }

    private function categories(): Collection
    {
        return AssetCategory::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);
    }

    private function locations(): Collection
    {
        return Location::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'type', 'floor_number']);
    }

    private function mapsForLocation(?int $locationId): Collection
    {
        if ($locationId === null) {
            return collect();
        }

        return LocationMap::query()
            ->where('location_id', $locationId)
            ->orderBy('name')
            ->get(['id', 'location_id', 'name', 'image_width', 'image_height']);
    }

    private function allMaps(): Collection
    {
        return LocationMap::query()
            ->orderBy('location_id')
            ->orderBy('name')
            ->get(['id', 'location_id', 'name', 'image_width', 'image_height']);
    }

    private function selectedLocationId(?Asset $asset = null): ?int
    {
        $selected = old('current_location_id', $asset?->current_location_id);

        if ($selected === null || $selected === '') {
            return null;
        }

        return (int) $selected;
    }
}
