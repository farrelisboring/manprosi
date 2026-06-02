<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Models\LocationMap;
use App\Services\LocationDeletionGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
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
            ->with('locationMap:id,name')
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
        $location = Location::create($this->payloadWithDenahPath($request->validated(), $request->file('denah_image')));

        return redirect()
            ->route('web.locations.show', $location)
            ->with('status_message', 'Location created successfully.')
            ->with('status_type', 'success');
    }

    public function show(Location $location): View
    {
        return view('locations.show', [
            'location' => $location->load([
                'locationMap:id,name,notes,created_at',
            ]),
            'isDeletionBlocked' => $this->deletionGuard->isBlocked($location),
            'blockedDeletionMessage' => LocationDeletionGuard::BLOCKED_MESSAGE,
        ]);
    }

    public function denah(Location $location): Response
    {
        abort_unless($location->hasDenahImage(), 404);

        return response($location->denah_image_data, 200, [
            'Content-Type' => $location->denah_image_mime_type,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function edit(Location $location): View
    {
        return view('locations.edit', $this->formViewData($location));
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $location->update($this->payloadWithDenahPath($request->validated(), $request->file('denah_image')));

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
            'locationMapOptions' => $this->locationMapOptions(),
        ];
    }

    private function locationMapOptions(): Collection
    {
        return LocationMap::query()
            ->orderBy('name')
            ->select(['id', 'name'])
            ->get();
    }

    private function blockedDeletionIds(LengthAwarePaginator $locations): array
    {
        return collect($locations->items())
            ->filter(fn (Location $location) => $this->deletionGuard->isBlocked($location))
            ->map(fn (Location $location) => $location->id)
            ->all();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function payloadWithDenahPath(array $validated, ?UploadedFile $denahImage): array
    {
        unset($validated['denah_image']);

        if ($denahImage) {
            $validated['denah_image_data'] = file_get_contents($denahImage->getRealPath());
            $validated['denah_image_mime_type'] = $denahImage->getMimeType() ?: 'application/octet-stream';
        }

        return $validated;
    }
}
