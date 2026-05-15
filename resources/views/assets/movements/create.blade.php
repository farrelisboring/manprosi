@php
    $mapOptionsByLocation = $allMaps
        ->groupBy('location_id')
        ->map(fn ($group) => $group->map(fn ($map) => [
            'id' => $map->id,
            'name' => $map->name,
            'image_width' => $map->image_width,
            'image_height' => $map->image_height,
        ])->values())
        ->toArray();
@endphp

@extends('layouts.app')

@section('title', 'Record Movement - '.$asset->name.' | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-sm font-medium text-amber-700">Record movement</p>
            <h1 class="text-3xl font-semibold text-gray-950">{{ $asset->name }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $asset->asset_code }}{{ $asset->category ? ' - '.$asset->category->name : '' }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.tracking.show', $asset) }}">Back to tracking</a>
        </div>
    </section>

    <section class="mt-8 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-950">Current placement</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Current location</p>
                <p class="mt-1 text-sm text-gray-900">{{ $asset->currentLocation?->name ?? 'Unassigned' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Current map</p>
                <p class="mt-1 text-sm text-gray-900">{{ $asset->currentMap?->name ?? 'No map placement' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Position X</p>
                <p class="mt-1 text-sm text-gray-900">{{ $asset->position_x !== null ? number_format($asset->position_x, 4) : 'Not set' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Position Y</p>
                <p class="mt-1 text-sm text-gray-900">{{ $asset->position_y !== null ? number_format($asset->position_y, 4) : 'Not set' }}</p>
            </div>
        </div>
    </section>

    <form
        action="{{ route('web.assets.movements.store', $asset) }}"
        class="mt-8 grid gap-6 lg:grid-cols-2"
        method="POST"
        data-map-dependent-form
        data-map-options='@json($mapOptionsByLocation)'
    >
        @csrf

        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Movement details</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-900" for="to_location_id">Destination location</label>
                        <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="to_location_id" name="to_location_id" data-location-select>
                            <option value="">Select a destination</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected((string) $selectedDestinationLocationId === (string) $location->id)>
                                    {{ $location->name }} ({{ $location->code }}){{ $location->floor_number !== null ? ' - Floor '.$location->floor_number : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('to_location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="movement_source">Movement source</label>
                        <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="movement_source" name="movement_source">
                            <option value="">Use default source</option>
                            @foreach ($movementSources as $movementSource)
                                <option value="{{ $movementSource->value }}" @selected(old('movement_source') === $movementSource->value)>
                                    {{ str($movementSource->value)->headline() }}
                                </option>
                            @endforeach
                        </select>
                        @error('movement_source') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="moved_by_user_id">Moved by user</label>
                        <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="moved_by_user_id" name="moved_by_user_id">
                            <option value="">Leave unattributed</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((string) old('moved_by_user_id') === (string) $user->id)>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('moved_by_user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="reason">Reason</label>
                        <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="reason" name="reason" type="text" value="{{ old('reason') }}">
                        @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="moved_at">Moved at</label>
                        <input
                            class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                            id="moved_at"
                            name="moved_at"
                            type="datetime-local"
                            min="{{ \Illuminate\Support\Carbon::parse(\App\Http\Requests\StoreAssetMovementRequest::EARLIEST_MOVED_AT)->format('Y-m-d\TH:i') }}"
                            max="{{ \Illuminate\Support\Carbon::parse(\App\Http\Requests\StoreAssetMovementRequest::LATEST_MOVED_AT)->format('Y-m-d\TH:i') }}"
                            value="{{ old('moved_at') ? \Illuminate\Support\Carbon::parse(old('moved_at'))->format('Y-m-d\TH:i') : '' }}"
                        >
                        <p class="mt-1 text-xs text-gray-500">Choose a date between 1970-01-01 00:00 and 2038-01-19 03:14.</p>
                        @error('moved_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-900" for="notes">Notes</label>
                        <textarea class="mt-2 block min-h-28 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="notes" name="notes">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Destination placement</h2>
                <p class="mt-1 text-sm text-gray-600">Optionally attach a map placement for the destination location.</p>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-900" for="current_map_id">Destination map</label>
                        <select
                            class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200 disabled:bg-gray-100 disabled:text-gray-500"
                            id="current_map_id"
                            name="current_map_id"
                            data-map-select
                            data-selected-map="{{ old('current_map_id') }}"
                            @disabled($selectedDestinationLocationId === null || $maps->isEmpty())
                        >
                            <option value="">{{ $selectedDestinationLocationId === null ? 'Choose a destination first' : 'No map placement' }}</option>
                            @foreach ($maps as $map)
                                <option value="{{ $map->id }}" @selected((string) old('current_map_id') === (string) $map->id)>
                                    {{ $map->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('current_map_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="position_x">Position X</label>
                        <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="position_x" name="position_x" step="0.0001" type="number" value="{{ old('position_x') }}">
                        @error('position_x') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="position_y">Position Y</label>
                        <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="position_y" name="position_y" step="0.0001" type="number" value="{{ old('position_y') }}">
                        @error('position_y') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Submission</h2>
                <p class="mt-2 text-sm text-gray-600">
                    The origin location is derived automatically from the asset's current placement when the movement is saved.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Save movement</button>
                    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.tracking.show', $asset) }}">Cancel</a>
                </div>
            </section>
        </div>
    </form>
@endsection
