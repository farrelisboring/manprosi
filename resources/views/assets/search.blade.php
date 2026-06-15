@extends('layouts.app')

@section('title', 'Search Assets | Hospital Asset Manager')
@section('page-eyebrow', 'Modul Pencarian')
@section('page-heading', 'Pencarian Aset')

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.assets.index') }}">
        Browse Asset
    </a>
@endsection

@section('content')
    <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm lg:p-6">
        <form action="{{ route('web.asset-search.index') }}" class="grid gap-4 lg:grid-cols-[2fr_1fr_1fr_1fr_auto]">
            <div>
                <label class="block text-sm font-medium text-gray-900" for="search">Search</label>
                <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" id="search" name="search" type="text" value="{{ request('search') }}" placeholder="Nama Asset">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="category_id">Category</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" id="category_id" name="category_id">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="current_location_id">Location</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" id="current_location_id" name="current_location_id">
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
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" id="status" name="status">
                    <option value="">Semua Status</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                            {{ str($status->value)->headline() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-3">
                <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Telusuri</button>
                <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.asset-search.index') }}">Reset</a>
            </div>
        </form>
    </section>

    @if (! $hasSearch)
        <section class="mt-6 rounded-[28px] border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Mulai Pencarian</p>
            <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">Mulai dengan kata kunci</h2>
            <p class="mt-3 text-sm leading-7 text-slate-600">
                Gunakan nama aset, kode aset, barcode, QR, RFID, atau kategori untuk menemukan perangkat yang dibutuhkan.
            </p>
        </section>
    @else
        <section class="mt-6 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">Hasil Pencarian</h2>
                    <p class="text-sm text-gray-600">
                        {{ number_format($assets->total()) }} hasil ditemukan
                        @if($searchTerm)
                            untuk <span class="font-medium text-gray-950">"{{ $searchTerm }}"</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Asset</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Current placement</th>
                            <th class="px-4 py-3 font-medium">Codes</th>
                            <th class="px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($assets as $asset)
                            <tr class="bg-white align-top">
                                <td class="px-4 py-4">
                                    <a class="font-medium text-gray-950 hover:text-violet-800" href="{{ route('web.assets.show', $asset) }}">{{ $asset->name }}</a>
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
                                    <p>{{ $asset->currentLocation?->name ?? 'Unassigned' }}</p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $asset->currentMap?->name ?? 'No map placement' }} -
                                        {{ $asset->hasMapPlacement() ? 'coordinates set' : 'placement incomplete' }}
                                    </p>
                                </td>
                                <td class="px-4 py-4 text-xs text-gray-600">
                                    <p>Barcode: {{ $asset->barcode_value ?: 'None' }}</p>
                                    <p class="mt-1">RFID: {{ $asset->rfid_tag ?: 'None' }}</p>
                                    <p class="mt-1">QR: {{ $asset->qr_code_value ?: 'None' }}</p>
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
                            <td class="px-4 py-10 text-center text-gray-600" colspan="6">Tidak ada aset yang cocok untuk filter saat ini.</td>
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
@endsection
