<div class="space-y-6" data-location-assets-panel>
    @if (! $hasLocations)
        <section class="rounded-lg border border-amber-200 bg-amber-50 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-amber-950">Location viewer is blocked</h2>
            <p class="mt-2 text-sm text-amber-900">There are no active locations available yet, so this viewer cannot show any current assignments.</p>
        </section>
    @elseif (! $selectedLocation)
        <section class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Choose a location to begin</h2>
            <p class="mt-2 text-sm text-gray-600">Select one active location from the dropdown above to see its directly assigned assets.</p>
        </section>
    @else
        <section class="grid gap-4 xl:grid-cols-[1.4fr_repeat(4,minmax(0,1fr))]">
            <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Selected location</p>
                <p class="mt-3 text-lg font-semibold text-gray-950">{{ $selectedLocation->name }}</p>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $selectedLocation->code }} - {{ str($selectedLocation->type)->headline() }}
                    @if ($selectedLocation->floor_number !== null)
                        - Floor {{ $selectedLocation->floor_number }}
                    @endif
                </p>
            </article>

            <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Total assets</p>
                <p class="mt-3 text-3xl font-semibold text-gray-950">{{ number_format($statusCounts['total']) }}</p>
            </article>

            <article class="rounded-lg border border-emerald-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-700">Available</p>
                <p class="mt-3 text-3xl font-semibold text-emerald-900">{{ number_format($statusCounts['available']) }}</p>
            </article>

            <article class="rounded-lg border border-sky-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-sky-700">In use</p>
                <p class="mt-3 text-3xl font-semibold text-sky-900">{{ number_format($statusCounts['in_use']) }}</p>
            </article>

            <article class="rounded-lg border border-amber-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-700">Maintenance</p>
                <p class="mt-3 text-3xl font-semibold text-amber-900">{{ number_format($statusCounts['maintenance']) }}</p>
            </article>
        </section>

        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-gray-200 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">Current asset assignments</h2>
                    <p class="text-sm text-gray-600">Only assets whose current location exactly matches {{ $selectedLocation->name }}.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Asset</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Map placement</th>
                            <th class="px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($assets as $asset)
                            <tr class="bg-white align-top">
                                <td class="px-4 py-4">
                                    <a class="font-medium text-gray-950 hover:text-amber-800" href="{{ route('web.assets.show', $asset) }}">{{ $asset->name }}</a>
                                    <p class="text-xs text-gray-500">{{ $asset->asset_code }}</p>
                                    @if ($asset->brand || $asset->model)
                                        <p class="mt-1 text-xs text-gray-500">{{ trim(($asset->brand ?? '').' '.($asset->model ?? '')) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-gray-700">{{ $asset->category?->name ?? 'Uncategorized' }}</td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ str($asset->status?->value ?? 'unknown')->headline() }}</span>
                                </td>
                                <td class="px-4 py-4 text-gray-700">
                                    <p>{{ $asset->currentMap?->name ?? 'No map placement' }}</p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $asset->hasMapPlacement() ? 'Coordinates are set.' : 'Placement is incomplete.' }}
                                    </p>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.show', $asset) }}">View</a>
                                        <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.tracking.show', $asset) }}">Tracking</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-10 text-center text-gray-600" colspan="5">No assets are currently assigned to this location.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-4">
                {{ $assets->links() }}
            </div>
        </section>
    @endif
</div>
