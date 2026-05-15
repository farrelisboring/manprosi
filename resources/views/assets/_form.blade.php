@php
    $asset = $asset ?? null;
    $selectedMapId = old('current_map_id', $asset?->current_map_id);
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

<div
    class="grid gap-6 lg:grid-cols-2"
    data-map-dependent-form
    data-map-options='@json($mapOptionsByLocation)'
>
    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900" for="asset_code">Asset code</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="asset_code" name="asset_code" type="text" value="{{ old('asset_code', $asset?->asset_code) }}">
                    @error('asset_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900" for="name">Asset name</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="name" name="name" type="text" value="{{ old('name', $asset?->name) }}">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900" for="category_id">Category</label>
                    <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="category_id" name="category_id">
                        <option value="">Select a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $asset?->category_id) === (string) $category->id)>
                                {{ $category->name }} ({{ $category->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900" for="status">Status</label>
                    <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="status" name="status">
                        <option value="">Use default status</option>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $asset?->status?->value) === $status->value)>
                                {{ str($status->value)->headline() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900" for="description">Description</label>
                    <textarea class="mt-2 block min-h-28 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="description" name="description">{{ old('description', $asset?->description) }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Identifiers and vendor details</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-900" for="brand">Brand</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="brand" name="brand" type="text" value="{{ old('brand', $asset?->brand) }}">
                    @error('brand') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900" for="model">Model</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="model" name="model" type="text" value="{{ old('model', $asset?->model) }}">
                    @error('model') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900" for="serial_number">Serial number</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="serial_number" name="serial_number" type="text" value="{{ old('serial_number', $asset?->serial_number) }}">
                    @error('serial_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900" for="barcode_value">Barcode value</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="barcode_value" name="barcode_value" type="text" value="{{ old('barcode_value', $asset?->barcode_value) }}">
                    @error('barcode_value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900" for="rfid_tag">RFID tag</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="rfid_tag" name="rfid_tag" type="text" value="{{ old('rfid_tag', $asset?->rfid_tag) }}">
                    @error('rfid_tag') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>
    </div>

    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Placement</h2>
            <p class="mt-1 text-sm text-gray-600">Choose a current location first. Maps are limited to the selected location.</p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900" for="current_location_id">Current location</label>
                    <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="current_location_id" name="current_location_id" data-location-select>
                        <option value="">No current location</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected((string) $selectedLocationId === (string) $location->id)>
                                {{ $location->name }} ({{ $location->code }}){{ $location->floor_number !== null ? ' - Floor '.$location->floor_number : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('current_location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900" for="current_map_id">Current map</label>
                    <select
                        class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200 disabled:bg-gray-100 disabled:text-gray-500"
                        id="current_map_id"
                        name="current_map_id"
                        data-map-select
                        data-selected-map="{{ $selectedMapId }}"
                        @disabled($selectedLocationId === null || $maps->isEmpty())
                    >
                        <option value="">{{ $selectedLocationId === null ? 'Choose a location first' : 'No map placement' }}</option>
                        @foreach ($maps as $map)
                            <option value="{{ $map->id }}" @selected((string) $selectedMapId === (string) $map->id)>
                                {{ $map->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('current_map_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900" for="position_x">Position X</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="position_x" name="position_x" step="0.0001" type="number" value="{{ old('position_x', $asset?->position_x) }}">
                    @error('position_x') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900" for="position_y">Position Y</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="position_y" name="position_y" step="0.0001" type="number" value="{{ old('position_y', $asset?->position_y) }}">
                    @error('position_y') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Operational notes</h2>
            <div class="mt-5">
                <label class="block text-sm font-medium text-gray-900" for="notes">Notes</label>
                <textarea class="mt-2 block min-h-40 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="notes" name="notes">{{ old('notes', $asset?->notes) }}</textarea>
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </section>

        @if ($blockedByMissingCategories)
            <section class="rounded-lg border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-amber-900">Asset creation is blocked</h2>
                <p class="mt-2 text-sm text-amber-800">
                    Create at least one asset category before adding inventory records through this screen.
                </p>
            </section>
        @endif
    </div>
</div>

<div class="mt-8 flex flex-wrap items-center gap-3">
    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:bg-gray-400" type="submit" @disabled($blockedByMissingCategories)>
        {{ $submitLabel }}
    </button>
    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ $asset ? route('web.assets.show', $asset) : route('web.assets.index') }}">
        Cancel
    </a>
</div>
