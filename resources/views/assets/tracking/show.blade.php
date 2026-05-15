@extends('layouts.app')

@section('title', 'Tracking - '.$asset->name.' | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-sm font-medium text-sky-700">Asset tracking</p>
            <h1 class="text-3xl font-semibold text-gray-950">{{ $asset->name }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $asset->asset_code }}{{ $asset->category ? ' - '.$asset->category->name : '' }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.show', $asset) }}">Asset detail</a>
            <a class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" href="{{ route('web.assets.movements.create', $asset) }}">Record movement</a>
        </div>
    </section>

    <section class="mt-8 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <form action="{{ route('web.assets.tracking.show', $asset) }}" class="grid gap-4 lg:flex-1 lg:grid-cols-[1.2fr_1fr_1fr_1fr_1fr_auto] lg:items-end">
                <div class="flex h-full flex-col justify-end">
                    <label class="block text-sm font-medium text-gray-900" for="movement_source">Movement source</label>
                    <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="movement_source" name="movement_source">
                        <option value="">All sources</option>
                        @foreach ($movementSources as $movementSource)
                            <option value="{{ $movementSource->value }}" @selected(request('movement_source') === $movementSource->value)>
                                {{ str($movementSource->value)->headline() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex h-full flex-col justify-end">
                    <label class="block text-sm font-medium text-gray-900" for="from_location_id">From location</label>
                    <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="from_location_id" name="from_location_id">
                        <option value="">Any origin</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected((string) request('from_location_id') === (string) $location->id)>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex h-full flex-col justify-end">
                    <label class="block text-sm font-medium text-gray-900" for="to_location_id">To location</label>
                    <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="to_location_id" name="to_location_id">
                        <option value="">Any destination</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected((string) request('to_location_id') === (string) $location->id)>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex h-full flex-col justify-end">
                    <label class="block text-sm font-medium text-gray-900" for="date_from">Date from</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="date_from" name="date_from" type="date" value="{{ request('date_from') }}">
                </div>

                <div class="flex h-full flex-col justify-end">
                    <label class="block text-sm font-medium text-gray-900" for="date_to">Date to</label>
                    <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="date_to" name="date_to" type="date" value="{{ request('date_to') }}">
                </div>

                <div class="flex h-full items-end gap-3">
                    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Apply</button>
                    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.tracking.show', $asset) }}">Reset</a>
                </div>
            </form>

            <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3" data-tracking-poller data-refresh-url="{{ $refreshUrl }}">
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
