<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationMapRequest;
use App\Http\Requests\UpdateLocationMapRequest;
use App\Models\Location;
use App\Models\LocationMap;
use App\Services\LocationMapDeletionGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class LocationMapController extends Controller
{
    public function __construct(private readonly LocationMapDeletionGuard $deletionGuard)
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->whereNull('deleted_at')],
        ]);

        $maps = LocationMap::query()
            ->with('location:id,code,name,type,floor_number')
            ->when($validated['location_id'] ?? null, fn ($query, $locationId) => $query->where('location_id', $locationId))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('location-maps.index', [
            'maps' => $maps,
            'locations' => $this->locations(),
            'blockedDeletionIds' => $this->blockedDeletionIds($maps),
        ]);
    }

    public function create(Request $request): View
    {
        return view('location-maps.create', $this->formViewData($request));
    }

    public function store(StoreLocationMapRequest $request): RedirectResponse
    {
        $locationMap = LocationMap::create($request->validated());

        return redirect()
            ->route('web.location-maps.show', $locationMap)
            ->with('status_message', 'Location map created successfully.')
            ->with('status_type', 'success');
    }

    public function show(LocationMap $locationMap): View
    {
        return view('location-maps.show', [
            'locationMap' => $locationMap->load(['location:id,code,name,type,floor_number', 'assets:id,name,asset_code,current_map_id']),
            'isDeletionBlocked' => $this->deletionGuard->isBlocked($locationMap),
            'blockedDeletionMessage' => LocationMapDeletionGuard::BLOCKED_MESSAGE,
        ]);
    }

    public function edit(Request $request, LocationMap $locationMap): View
    {
        return view('location-maps.edit', $this->formViewData($request, $locationMap));
    }

    public function update(UpdateLocationMapRequest $request, LocationMap $locationMap): RedirectResponse
    {
        $locationMap->update($request->validated());

        return redirect()
            ->route('web.location-maps.show', $locationMap)
            ->with('status_message', 'Location map updated successfully.')
            ->with('status_type', 'success');
    }

    public function destroy(LocationMap $locationMap): RedirectResponse
    {
        if ($this->deletionGuard->isBlocked($locationMap)) {
            return redirect()
                ->route('web.location-maps.show', $locationMap)
                ->with('status_message', LocationMapDeletionGuard::BLOCKED_MESSAGE)
                ->with('status_type', 'error');
        }

        $locationMap->delete();

        return redirect()
            ->route('web.location-maps.index', ['location_id' => $locationMap->location_id])
            ->with('status_message', 'Location map deleted successfully.')
            ->with('status_type', 'success');
    }

    private function formViewData(Request $request, ?LocationMap $locationMap = null): array
    {
        $selectedLocationId = old('location_id', $locationMap?->location_id ?? $request->query('location_id'));

        return [
            'locationMap' => $locationMap?->loadMissing('location:id,code,name'),
            'locations' => $this->locations(),
            'selectedLocationId' => $selectedLocationId === '' ? null : $selectedLocationId,
        ];
    }

    private function locations(): Collection
    {
        return Location::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'type', 'floor_number']);
    }

    private function blockedDeletionIds(LengthAwarePaginator $maps): array
    {
        return collect($maps->items())
            ->filter(fn (LocationMap $locationMap) => $this->deletionGuard->isBlocked($locationMap))
            ->map(fn (LocationMap $locationMap) => $locationMap->id)
            ->all();
    }
}
