@csrf

@if ($locationMap)
    @method('PUT')
@endif

<div class="grid gap-6 lg:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-900" for="name">Nama Gedung</label>
        <input
            class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
            id="name"
            maxlength="255"
            name="name"
            required
            type="text"
            value="{{ old('name', $locationMap?->name) }}"
        >
    </div>
</div>

<div class="mt-6">
    <label class="block text-sm font-medium text-gray-900" for="notes">Catatan</label>
    <textarea class="mt-2 block min-h-32 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200" id="notes" name="notes">{{ old('notes', $locationMap?->notes) }}</textarea>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">
        {{ $submitLabel }}
    </button>
    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ $cancelUrl }}">
        Batal
    </a>
</div>
