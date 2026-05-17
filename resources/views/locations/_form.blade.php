@csrf

@if ($location)
    @method('PUT')
@endif

<div class="grid gap-6 lg:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-900" for="location_map_id">Gedung</label>
        <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200" id="location_map_id" name="location_map_id">
            <option value="">Pilih jika ada</option>
            @foreach ($locationMapOptions as $locationMapOption)
                <option value="{{ $locationMapOption->id }}" @selected((string) old('location_map_id', $location?->location_map_id) === (string) $locationMapOption->id)>
                    {{ $locationMapOption->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-900" for="code">Kode Ruangan</label>
        <input
            class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"
            id="code"
            maxlength="255"
            name="code"
            required
            type="text"
            value="{{ old('code', $location?->code) }}"
        >
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-900" for="name">Nama Ruangan</label>
        <input
            class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"
            id="name"
            maxlength="255"
            name="name"
            required
            type="text"
            value="{{ old('name', $location?->name) }}"
        >
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-900" for="type">Tipe</label>
        <input
            class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"
            id="type"
            maxlength="30"
            name="type"
            required
            type="text"
            value="{{ old('type', $location?->type) }}"
        >
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-900" for="floor_number">Lantai</label>
        <input
            class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"
            id="floor_number"
            name="floor_number"
            type="number"
            value="{{ old('floor_number', $location?->floor_number) }}"
        >
    </div>

    <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-4 lg:col-span-2">
        <input name="is_active" type="hidden" value="0">
        <input
            class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
            id="is_active"
            name="is_active"
            type="checkbox"
            value="1"
            @checked(old('is_active', $location?->is_active ?? true))
        >
        <label class="text-sm font-medium text-gray-900" for="is_active">Tetap aktif dan bisa dipilih di aplikasi</label>
    </div>
</div>

<div class="mt-6">
    <label class="block text-sm font-medium text-gray-900" for="description">Deskripsi</label>
    <textarea class="mt-2 block min-h-32 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200" id="description" name="description">{{ old('description', $location?->description) }}</textarea>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">
        {{ $submitLabel }}
    </button>
    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ $cancelUrl }}">
        Batal
    </a>
</div>
