<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AssetSearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $hasSearch = $request->hasAny(['search', 'category_id', 'current_location_id', 'status']);

        return view('assets.search', [
            'searchTerm' => $request->string('search')->toString(),
            'hasSearch' => $hasSearch,
            'assets' => $this->assets($request, $hasSearch),
            'categories' => $this->categories(),
            'locations' => $this->locations(),
            'statusOptions' => AssetStatus::cases(),
        ]);
    }

    private function assets(Request $request, bool $hasSearch): LengthAwarePaginator
    {
        if (! $hasSearch) {
            return new LengthAwarePaginator([], 0, 15, 1, [
                'path' => $request->url(),
                'pageName' => 'page',
            ]);
        }

        return Asset::query()
            ->with(['category', 'currentLocation', 'currentMap'])
            ->withFilters($request->only(['search', 'category_id', 'current_location_id', 'status']))
            ->latest()
            ->paginate(15)
            ->withQueryString();
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
}
