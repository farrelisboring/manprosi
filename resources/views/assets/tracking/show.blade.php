@extends('layouts.app')

@section('title', 'Tracking - '.$asset->name.' | Hospital Asset Manager')
@section('page-eyebrow', 'Tracking Aset')
@section('page-heading')
    <span class="text-3xl lg:text-4xl">{{ $asset->name }}</span>
@endsection

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.assets.show', $asset) }}">Asset detail</a>
    <a class="rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" href="{{ route('web.assets.movements.create', $asset) }}">Record movement</a>
@endsection

@section('content')
    <section class="rounded-[28px] border border-slate-200 bg-white px-6 py-5 shadow-sm">
        <p class="text-sm text-slate-600">{{ $asset->asset_code }}{{ $asset->category ? ' - '.$asset->category->name : '' }}</p>
    </section>

    <section class="mt-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <form action="{{ route('web.assets.tracking.show', $asset) }}" class="grid gap-4 lg:flex-1 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end">
                <div class="flex h-full flex-col justify-end">
                    <label class="block text-sm font-medium text-gray-900" for="from_location_id">Dari Ruangan</label>
                    <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="from_location_id" name="from_location_id">
                        <option value="">Any origin</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected((string) ($trackingFilters['from_location_id'] ?? '') === (string) $location->id)>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex h-full flex-col justify-end">
                    <label class="block text-sm font-medium text-gray-900" for="to_location_id">Destinasi Ruangan</label>
                    <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="to_location_id" name="to_location_id">
                        <option value="">Any destination</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected((string) ($trackingFilters['to_location_id'] ?? '') === (string) $location->id)>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex h-full flex-col justify-end">
                    <label class="block text-sm font-medium text-gray-900" for="date_from">Dari Tanggal</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="date_from" name="date_from" type="date" value="{{ $trackingFilters['date_from'] ?? '' }}">
                </div>

                <div class="flex h-full flex-col justify-end">
                    <label class="block text-sm font-medium text-gray-900" for="date_to">Ke Tanggal</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="date_to" name="date_to" type="date" value="{{ $trackingFilters['date_to'] ?? '' }}">
                </div>

                <div class="flex h-full items-end gap-3">
                    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Filter</button>
                    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.tracking.show', $asset) }}">Reset</a>
                </div>
            </form>

            <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3" data-poller data-refresh-url="{{ $refreshUrl }}" data-refresh-container="[data-tracking-panel-container]">
                <div>
                    <p class="text-sm font-medium text-gray-900">Auto-refresh</p>
                    <p class="text-xs text-gray-600">Refresh placement and movement history every 5 seconds.</p>
                </div>
                <button class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" data-poll-toggle type="button" aria-pressed="false">
                    Off
                </button>
            </div>
        </div>
    </section>

    <div class="mt-6" data-tracking-panel-container>
        @include('assets.tracking._panel', ['asset' => $asset, 'latestMovement' => $latestMovement, 'movements' => $movements])
    </div>
@endsection
