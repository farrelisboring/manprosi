@extends('layouts.app')

@section('title', $locationMap->name.' | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-sm font-medium text-cyan-700">Location map detail</p>
            <h1 class="text-3xl font-semibold text-gray-950">{{ $locationMap->name }}</h1>
            <p class="mt-2 text-sm text-gray-600">
                @if ($locationMap->location)
                    {{ $locationMap->location->name }} · {{ $locationMap->location->code }}
                @else
                    No linked location
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            @if ($locationMap->location)
                <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.locations.show', $locationMap->location) }}">View location</a>
                <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.index', ['location_id' => $locationMap->location->id]) }}">More maps for this location</a>
            @else
                <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.index') }}">Back to maps</a>
            @endif
            <a class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" href="{{ route('web.location-maps.edit', $locationMap) }}">Edit map</a>
        </div>
    </section>

    <section class="mt-8 grid gap-8 xl:grid-cols-[1.4fr_1fr]">
        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Map snapshot</h2>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Location</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if ($locationMap->location)
                                <a class="hover:text-amber-800" href="{{ route('web.locations.show', $locationMap->location) }}">{{ $locationMap->location->name }}</a>
                            @else
                                Unknown location
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Floor</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $locationMap->location?->floor_number !== null ? 'Floor '.$locationMap->location->floor_number : 'Not set' }}</dd>
                    </div>
                </dl>

                <div class="mt-5 border-t border-gray-200 pt-5">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Notes</dt>
                    <dd class="mt-1 text-sm text-gray-700">{{ $locationMap->notes ?: 'No notes recorded.' }}</dd>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-950">Assets using this map</h2>
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ $locationMap->assets->count() }}</span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($locationMap->assets as $asset)
                        <div class="rounded-lg border border-gray-200 px-4 py-3">
                            <a class="font-medium text-gray-950 hover:text-sky-800" href="{{ route('web.assets.show', $asset) }}">{{ $asset->name }}</a>
                            <p class="mt-1 text-sm text-gray-600">{{ $asset->asset_code }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">No assets currently reference this map.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Record actions</h2>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.index', $locationMap->location ? ['location_id' => $locationMap->location->id] : []) }}">Back to maps</a>

                    @if ($isDeletionBlocked)
                        <button
                            class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:border-red-400 hover:text-red-900"
                            data-blocked-action-message="{{ $blockedDeletionMessage }}"
                            type="button"
                        >
                            Delete map
                        </button>
                    @else
                        <form action="{{ route('web.location-maps.destroy', $locationMap) }}" method="POST" onsubmit="return confirm('Delete this location map?');">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:border-red-400 hover:text-red-900" type="submit">Delete map</button>
                        </form>
                    @endif
                </div>
            </section>
        </div>
    </section>
@endsection
