<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetGeofence;
use App\Models\Location;
use App\Models\LocationMap;
use App\Services\AssetGeofenceRuleSyncService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

    public function store(StoreAssetRequest $request, AssetGeofenceRuleSyncService $geofenceRuleSyncService): RedirectResponse
    {
        $validated = $request->validated();
        $asset = Asset::create($this->assetPayload($validated));
        $geofenceRuleSyncService->sync($asset, $this->geofencePayload($validated));

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

    public function update(UpdateAssetRequest $request, Asset $asset, AssetGeofenceRuleSyncService $geofenceRuleSyncService): RedirectResponse
    {
        $validated = $request->validated();
        $asset->update($this->assetPayload($validated));
        $geofenceRuleSyncService->sync($asset, $this->geofencePayload($validated));

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
            'asset' => $asset?->loadMissing(['category', 'currentLocation', 'currentMap', 'geofences']),
            'categories' => $categories,
            'locations' => $this->locations(),
            'maps' => $this->mapsForLocation($selectedLocationId),
            'allMaps' => $this->allMaps(),
            'selectedLocationId' => $selectedLocationId,
            'statusOptions' => AssetStatus::cases(),
            'blockedByMissingCategories' => $categories->isEmpty(),
            'geofenceFormState' => $this->geofenceFormState($asset),
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

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function assetPayload(array $validated): array
    {
        return Arr::except($validated, [
            'geofence_enabled',
            'geofence_on_room_change',
            'geofence_forbidden_location_ids',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{enabled: bool, notify_on_room_change: bool, forbidden_location_ids: array<int, int>}
     */
    private function geofencePayload(array $validated): array
    {
        return [
            'enabled' => (bool) ($validated['geofence_enabled'] ?? false),
            'notify_on_room_change' => (bool) ($validated['geofence_on_room_change'] ?? false),
            'forbidden_location_ids' => array_values(array_map('intval', $validated['geofence_forbidden_location_ids'] ?? [])),
        ];
    }

    private function geofenceFormState(?Asset $asset = null): array
    {
        $assetGeofences = $asset?->geofences ?? collect();
        $roomChangeRule = $assetGeofences->contains(fn (AssetGeofence $geofence) => $geofence->rule_type === \App\Enums\GeofenceRuleType::RoomChangeNotification);
        $forbiddenLocationIds = $assetGeofences
            ->filter(fn (AssetGeofence $geofence) => $geofence->rule_type === \App\Enums\GeofenceRuleType::RestrictedEntry)
            ->pluck('location_id')
            ->filter()
            ->map(fn (mixed $id) => (int) $id)
            ->values()
            ->all();

        return [
            'enabled' => old('geofence_enabled', $asset ? $assetGeofences->isNotEmpty() : false),
            'notify_on_room_change' => old('geofence_on_room_change', $roomChangeRule),
            'forbidden_location_ids' => old('geofence_forbidden_location_ids', $forbiddenLocationIds),
        ];
    }
}
