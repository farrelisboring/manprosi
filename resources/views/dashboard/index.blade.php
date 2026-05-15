@extends('layouts.app')

@section('title', 'Dashboard | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-emerald-700">Operations dashboard</p>
            <h1 class="text-3xl font-semibold text-gray-950">Hospital assets at a glance</h1>
            <p class="mt-2 max-w-3xl text-sm text-gray-600">
                Keep the main asset inventory moving through one controller-based workflow while the older API layer winds down in the background.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" href="{{ route('web.assets.create') }}">
                Add asset
            </a>
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.index') }}">
                Browse assets
            </a>
        </div>
    </section>

    <section class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Total assets</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950">{{ number_format($totalAssets) }}</p>
        </article>

        <article class="rounded-lg border border-emerald-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-emerald-700">Available</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-900">{{ number_format($statusCounts['available']) }}</p>
        </article>

        <article class="rounded-lg border border-sky-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-sky-700">In use</p>
            <p class="mt-3 text-3xl font-semibold text-sky-900">{{ number_format($statusCounts['in_use']) }}</p>
        </article>

        <article class="rounded-lg border border-amber-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-amber-700">Maintenance</p>
            <p class="mt-3 text-3xl font-semibold text-amber-900">{{ number_format($statusCounts['maintenance']) }}</p>
        </article>
    </section>

    <section class="mt-10 grid gap-8 lg:grid-cols-[1.7fr_1fr]">
        <div>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">Recent assets</h2>
                    <p class="text-sm text-gray-600">Newest records in the inventory.</p>
                </div>
                <a class="text-sm font-medium text-sky-700 hover:text-sky-900" href="{{ route('web.assets.index') }}">See all</a>
            </div>

            <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Asset</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Location</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($recentAssets as $asset)
                            <tr class="bg-white">
                                <td class="px-4 py-3">
                                    <a class="font-medium text-gray-950 hover:text-sky-800" href="{{ route('web.assets.show', $asset) }}">{{ $asset->name }}</a>
                                    <p class="text-xs text-gray-500">{{ $asset->asset_code }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $asset->category?->name ?? 'Uncategorized' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $asset->currentLocation?->name ?? 'Unassigned' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ str($asset->status?->value ?? 'unknown')->headline() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-gray-600" colspan="4">No assets have been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">What this app now does</h2>
                <ul class="mt-4 space-y-3 text-sm text-gray-600">
                    <li>Browse and filter the hospital asset inventory in a Blade-driven workflow.</li>
                    <li>Create and update full asset records with category, location, and map placement data.</li>
                    <li>Manage QR label lifecycle as dedicated actions from each asset record.</li>
                </ul>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Next likely follow-ups</h2>
                <ul class="mt-4 space-y-3 text-sm text-gray-600">
                    <li>Reference-data CRUD for categories and locations.</li>
                    <li>Repair-update and movement history UI flows.</li>
                    <li>Retirement of API endpoints once the Blade flows cover the team’s needs.</li>
                </ul>
            </section>
        </div>
    </section>
@endsection
