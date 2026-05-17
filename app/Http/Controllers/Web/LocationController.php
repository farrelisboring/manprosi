<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Services\LocationDeletionGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LocationController extends Controller
{
    public function __construct(private readonly LocationDeletionGuard $deletionGuard)
    {
    }

    public function index(): View
    {
        $locations = Location::query()
            ->with('parent:id,code,name')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('locations.index', [
            'locations' => $locations,
            'blockedDeletionIds' => $this->blockedDeletionIds($locations),
        ]);
    }

    public function create(): View
    {
        return view('locations.create', $this->formViewData());
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $location = Location::create($request->validated());

        return redirect()
            ->route('web.locations.show', $location)
            ->with('status_message', 'Location created successfully.')
            ->with('status_type', 'success');
    }

    public function show(Location $location): View
    {
        return view('locations.show', [
            'location' => $location->load([
                'parent:id,code,name',
                'children:id,parent_id,code,name,type,floor_number',
                'maps:id,location_id,name,notes,created_at',
            ]),
            'isDeletionBlocked' => $this->deletionGuard->isBlocked($location),
            'blockedDeletionMessage' => LocationDeletionGuard::BLOCKED_MESSAGE,
        ]);
    }

    public function edit(Location $location): View
    {
        return view('locations.edit', $this->formViewData($location));
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()
            ->route('web.locations.show', $location)
            ->with('status_message', 'Location updated successfully.')
            ->with('status_type', 'success');
    }

    public function destroy(Location $location): RedirectResponse
    {
        if ($this->deletionGuard->isBlocked($location)) {
            return redirect()
                ->route('web.locations.show', $location)
                ->with('status_message', LocationDeletionGuard::BLOCKED_MESSAGE)
                ->with('status_type', 'error');
        }

        $location->delete();

        return redirect()
            ->route('web.locations.index')
            ->with('status_message', 'Location deleted successfully.')
            ->with('status_type', 'success');
    }

    private function formViewData(?Location $location = null): array
    {
        return [
            'location' => $location,
            'parentOptions' => $this->parentOptions($location),
        ];
    }

    private function parentOptions(?Location $location = null): Collection
    {
        $query = Location::query()
            ->orderBy('name')
            ->select(['id', 'code', 'name', 'type', 'floor_number']);

        if (! $location) {
            return $query->get();
        }

        return $query
            ->whereNotIn('id', array_merge([$location->id], $this->descendantIds($location)))
            ->get();
    }

    private function descendantIds(Location $location): array
    {
        $descendantIds = [];
        $pendingIds = [$location->id];

        while ($pendingIds !== []) {
            $childIds = Location::query()
                ->whereIn('parent_id', $pendingIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $childIds = array_values(array_diff($childIds, $descendantIds));

            if ($childIds === []) {
                break;
            }

            $descendantIds = array_merge($descendantIds, $childIds);
            $pendingIds = $childIds;
        }

        return $descendantIds;
    }

    private function blockedDeletionIds(LengthAwarePaginator $locations): array
    {
        return collect($locations->items())
            ->filter(fn (Location $location) => $this->deletionGuard->isBlocked($location))
            ->map(fn (Location $location) => $location->id)
            ->all();
    }
}
