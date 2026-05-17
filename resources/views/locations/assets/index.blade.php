@extends('layouts.app')

@section('title', 'Assets by Location | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-amber-700">Location operations</p>
            <h1 class="text-3xl font-semibold text-gray-950">Assets by location</h1>
            <p class="mt-2 max-w-3xl text-sm text-gray-600">
                Pick one active hospital location and review only the assets currently assigned there.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.locations.index') }}">Manage locations</a>
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.index') }}">Manage maps</a>
        </div>
    </section>

    <section class="mt-8 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <form action="{{ route('web.location-assets.index') }}" class="grid gap-4 lg:flex-1 lg:grid-cols-[minmax(0,2fr)_auto] lg:items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-900" for="location_id">Location</label>
                    <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200" id="location_id" name="location_id">
                        <option value="">Choose a location</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected((string) request('location_id') === (string) $location->id)>
                                {{ $location->name }} ({{ $location->code }}){{ $location->floor_number !== null ? ' - Floor '.$location->floor_number : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex h-full items-end gap-3">
                    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">View assets</button>
                    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-assets.index') }}">Reset</a>
                </div>
            </form>

            <div
                class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3"
                data-poller
                data-refresh-url="{{ $refreshUrl }}"
                data-refresh-container="[data-location-assets-panel-container]"
                @if (! $selectedLocation) data-poll-disabled="true" @endif
            >
                <div>
                    <p class="text-sm font-medium text-gray-900">Auto-refresh</p>
                    <p class="text-xs text-gray-600">Refresh the selected location summary and asset list every 5 seconds.</p>
                </div>
                <button class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400" data-poll-toggle type="button" aria-pressed="false" @disabled(! $selectedLocation)>
                    Off
                </button>
            </div>
        </div>
    </section>

    <div class="mt-6" data-location-assets-panel-container>
        @include('locations.assets._panel', [
            'selectedLocation' => $selectedLocation,
            'hasLocations' => $hasLocations,
            'statusCounts' => $statusCounts,
            'assets' => $assets,
        ])
    </div>
@endsection
