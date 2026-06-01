@php
    $asset = $asset ?? null;
    $selectedStatusValue = old('status', $asset?->status?->value ?? \App\Enums\AssetStatus::Available->value);
@endphp

<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900" for="asset_code">Kode Aset</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="asset_code" name="asset_code" type="text" value="{{ old('asset_code', $asset?->asset_code) }}">
                    @error('asset_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900" for="name">Nama Aset</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="name" name="name" type="text" value="{{ old('name', $asset?->name) }}">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900" for="category_id">Kategori</label>
                    <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="category_id" name="category_id">
                        <option value="">Pilih kategori</option>
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
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status->value }}" @selected($selectedStatusValue === $status->value)>
                                {{ str($status->value)->headline() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900" for="description">Deskripsi</label>
                    <textarea class="mt-2 block min-h-28 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="description" name="description">{{ old('description', $asset?->description) }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Detail lebih lanjut</h2>
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
                    <label class="block text-sm font-medium text-gray-900" for="serial_number">Nomer Serial</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="serial_number" name="serial_number" type="text" value="{{ old('serial_number', $asset?->serial_number) }}">
                    @error('serial_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>
    </div>

    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Placement</h2>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900" for="current_location_id">Lokasi saat ini</label>
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
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm" data-geofence-form>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">Peraturan Geofence</h2>
                    <p class="mt-2 text-sm text-gray-600">Atur kapan aset ini harus membuat notifikasi geofence tanpa memblokir perpindahan.</p>
                </div>
                <label class="inline-flex items-center gap-3 rounded-full border border-gray-200 px-4 py-2 text-sm font-medium text-gray-900">
                    <input name="geofence_enabled" type="hidden" value="0">
                    <input
                        class="h-4 w-4 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                        data-geofence-toggle
                        name="geofence_enabled"
                        type="checkbox"
                        value="1"
                        @checked((bool) $geofenceFormState['enabled'])
                    >
                    Aktifkan
                </label>
            </div>

            <fieldset class="mt-5 space-y-5" data-geofence-fieldset>
                <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-4">
                    <input name="geofence_on_room_change" type="hidden" value="0">
                    <input
                        class="mt-1 h-4 w-4 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                        name="geofence_on_room_change"
                        type="checkbox"
                        value="1"
                        @checked((bool) $geofenceFormState['notify_on_room_change'])
                    >
                    <span>
                        <span class="block text-sm font-medium text-gray-900">Ketika Pindah Ruangan</span>
                        <span class="mt-1 block text-sm text-gray-600">Buat notifikasi setiap kali aset berpindah ke ruangan lain.</span>
                    </span>
                </label>

                <div>
                    <p class="text-sm font-medium text-gray-900">Ruangan Terlarang</p>
                    <p class="mt-1 text-sm text-gray-600">Pilih ruangan yang akan memicu notifikasi jika aset dipindahkan ke sana.</p>

                    <div class="mt-4 max-h-64 space-y-3 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-4">
                        @forelse ($locations as $location)
                            <label class="flex items-start gap-3 rounded-md bg-white px-3 py-3 shadow-sm">
                                <input
                                    class="mt-1 h-4 w-4 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                    name="geofence_forbidden_location_ids[]"
                                    type="checkbox"
                                    value="{{ $location->id }}"
                                    @checked(in_array((int) $location->id, array_map('intval', (array) $geofenceFormState['forbidden_location_ids']), true))
                                >
                                <span>
                                    <span class="block text-sm font-medium text-gray-900">{{ $location->name }}</span>
                                    <span class="mt-1 block text-xs text-gray-500">{{ $location->code }}{{ $location->floor_number !== null ? ' - Floor '.$location->floor_number : '' }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-600">Belum ada ruangan aktif yang bisa dipilih.</p>
                        @endforelse
                    </div>
                    @error('geofence_forbidden_location_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('geofence_forbidden_location_ids.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </fieldset>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Notes Operasional</h2>
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
