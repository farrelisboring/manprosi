<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class LocationAssetController extends Controller
{
    public function index(Request $request): View
    {
        return view('locations.assets.index', $this->viewerViewData($request));
    }

    public function refresh(Request $request): View
    {
        return view('locations.assets._panel', $this->panelViewData($request));
    }

    private function viewerViewData(Request $request): array
    {
        return array_merge($this->panelViewData($request), [
            'locations' => $this->locations(),
            'refreshUrl' => route('web.location-assets.refresh'),
        ]);
    }

    private function panelViewData(Request $request): array
    {
        $validated = $request->validate($this->viewerRules());
        $selectedLocation = $this->selectedLocation($validated);

        return [
            'selectedLocation' => $selectedLocation,
            'hasLocations' => $this->locations()->isNotEmpty(),
            'statusCounts' => $this->statusCounts($selectedLocation),
            'assets' => $this->assetsForLocation($selectedLocation),
        ];
    }

    private function viewerRules(): array
    {
        return [
            'location_id' => [
                'nullable',
                'integer',
                Rule::exists('locations', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    private function selectedLocation(array $validated): ?Location
    {
        $locationId = $validated['location_id'] ?? null;

        if ($locationId === null) {
            return null;
        }

        return Location::query()
            ->active()
            ->whereKey($locationId)
            ->first(['id', 'code', 'name', 'type', 'floor_number']);
    }

    private function locations(): Collection
    {
        return Location::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'type', 'floor_number']);
    }

    private function statusCounts(?Location $location): array
    {
        $defaults = collect(AssetStatus::cases())
            ->mapWithKeys(fn (AssetStatus $status) => [$status->value => 0])
            ->all();

        if (! $location) {
            return [
                'total' => 0,
                ...$defaults,
            ];
        }

        $groupedCounts = Asset::query()
            ->atLocation($location)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count) => (int) $count)
            ->all();

        $mergedCounts = array_merge($defaults, $groupedCounts);

        return [
            'total' => array_sum($mergedCounts),
            ...$mergedCounts,
        ];
    }

    private function assetsForLocation(?Location $location): LengthAwarePaginator
    {
        if (! $location) {
            return new LengthAwarePaginator([], 0, 15, 1, [
                'path' => request()->url(),
                'pageName' => 'page',
            ]);
        }

        return Asset::query()
            ->with(['category', 'currentLocation', 'currentMap'])
            ->atLocation($location)
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }
}
