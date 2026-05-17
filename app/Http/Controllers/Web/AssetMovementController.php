<?php

namespace App\Http\Controllers\Web;

use App\Enums\MovementSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetMovementRequest;
use App\Models\Asset;
use App\Models\AssetMovement;
use App\Models\Location;
use App\Models\LocationMap;
use App\Models\User;
use App\Services\AssetMovementRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class AssetMovementController extends Controller
{
    public function show(Request $request, Asset $asset): View
    {
        return view('assets.tracking.show', $this->trackingViewData($request, $asset));
    }

    public function refresh(Request $request, Asset $asset): View
    {
        return view('assets.tracking._panel', $this->trackingPanelData($request, $asset));
    }

    public function create(Asset $asset): View
    {
        return view('assets.movements.create', [
            'asset' => $asset->load(['category', 'currentLocation', 'currentMap']),
            'locations' => $this->locations(),
            'maps' => $this->mapsForLocation($this->selectedDestinationLocationId()),
            'allMaps' => $this->allMaps(),
            'selectedDestinationLocationId' => $this->selectedDestinationLocationId(),
            'movementSources' => MovementSource::cases(),
            'users' => $this->users(),
        ]);
    }

    public function store(StoreAssetMovementRequest $request, Asset $asset, AssetMovementRecorder $recorder): RedirectResponse
    {
        $recorder->record($asset, $request->validated());

        return redirect()
            ->route('web.assets.tracking.show', $asset)
            ->with('status_message', 'Movement recorded successfully.')
            ->with('status_type', 'success');
    }

    private function trackingViewData(Request $request, Asset $asset): array
    {
        $panelData = $this->trackingPanelData($request, $asset);

        return array_merge($panelData, [
            'movementSources' => MovementSource::cases(),
            'locations' => $this->locations(),
            'refreshUrl' => route('web.assets.tracking.refresh', $asset),
        ]);
    }

    private function trackingPanelData(Request $request, Asset $asset): array
    {
        $validated = $request->validate($this->movementFilterRules());
        $asset = $asset->fresh()->load(['category', 'currentLocation', 'currentMap']);

        return [
            'asset' => $asset,
            'latestMovement' => $asset->movements()
                ->with(['fromLocation', 'toLocation', 'movedByUser'])
                ->newestFirst()
                ->first(),
            'movements' => AssetMovement::query()
                ->whereBelongsTo($asset)
                ->with(['asset', 'fromLocation', 'toLocation', 'movedByUser'])
                ->withFilters($validated)
                ->newestFirst()
                ->paginate($validated['per_page'] ?? 15)
                ->withQueryString(),
        ];
    }

    private function movementFilterRules(): array
    {
        return [
            'movement_source' => ['nullable', Rule::enum(MovementSource::class)],
            'from_location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')],
            'to_location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
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

    private function selectedDestinationLocationId(): ?int
    {
        $selected = old('to_location_id');

        if ($selected === null || $selected === '') {
            return null;
        }

        return (int) $selected;
    }

    private function users(): Collection
    {
        // TODO: Replace this manual selector with the authenticated current user once auth exists.
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
