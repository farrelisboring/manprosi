@extends('layouts.app')

@section('title', 'Daftar Aset | Hospital Asset Manager')

@section('content')
    @php
        $statusLabel = fn (?string $value) => match ($value) {
            'available' => 'Tersedia',
            'in_use' => 'Sedang Dipakai',
            'maintenance' => 'Perawatan',
            default => 'Tidak diketahui',
        };
    @endphp

    <section class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-sky-700">Persediaan</p>
            <h1 class="text-3xl font-semibold text-gray-950">Aset Rumah Sakit</h1>
        </div>

        <a class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" href="{{ route('web.assets.create') }}">
            Tambah Aset
        </a>
    </section>

    <section class="mt-8 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <form action="{{ route('web.assets.index') }}" class="grid gap-4 lg:grid-cols-[1fr_1fr_1fr_auto]">
            <div>
                <label class="block text-sm font-medium text-gray-900" for="category_id">Kategori</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="category_id" name="category_id">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="current_location_id">Lokasi</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="current_location_id" name="current_location_id">
                    <option value="">Semua Lokasi</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected((string) request('current_location_id') === (string) $location->id)>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="status">Status</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="status" name="status">
                    <option value="">Semua Status</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                            {{ $statusLabel($status->value) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-3 lg:justify-end">
                <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Filter</button>
                <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.index') }}">Atur Ulang</a>
            </div>
        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Aset</th>
                        <th class="px-4 py-3 font-medium">Kategori</th>
                        <th class="px-4 py-3 font-medium">Lokasi</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Kode</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($assets as $asset)
                        <tr class="bg-white align-top">
                            <td class="px-4 py-4">
                                <a class="font-medium text-gray-950 hover:text-sky-800" href="{{ route('web.assets.show', $asset) }}">{{ $asset->name }}</a>
                                <p class="text-xs text-gray-500">{{ $asset->asset_code }}</p>
                                @if ($asset->brand || $asset->model)
                                    <p class="mt-1 text-xs text-gray-500">{{ trim(($asset->brand ?? '').' '.($asset->model ?? '')) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-gray-700">{{ $asset->category?->name ?? 'Tanpa kategori' }}</td>
                            <td class="px-4 py-4 text-gray-700">
                                {{ $asset->currentLocation?->name ?? 'Belum ditetapkan' }}
                                @if ($asset->currentMap)
                                    <p class="mt-1 text-xs text-gray-500">{{ $asset->currentMap->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ $statusLabel($asset->status?->value) }}</span>
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-600">
                                <p>Barcode: {{ $asset->barcode_value ?: 'Belum ada' }}</p>
                                <p class="mt-1">RFID: {{ $asset->rfid_tag ?: 'Belum ada' }}</p>
                                <p class="mt-1">QR: {{ $asset->qr_code_value ?: 'Belum ada' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.show', $asset) }}">Lihat</a>
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.edit', $asset) }}">Ubah</a>
                                    <form action="{{ route('web.assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Hapus data aset ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-700 hover:border-red-400 hover:text-red-900" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-10 text-center text-gray-600" colspan="6">Belum ada aset yang cocok dengan filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-4 py-4">
            {{ $assets->links() }}
        </div>
    </section>
@endsection
