@extends('layouts.app')

@section('title', 'Report Damage | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-sm font-medium text-rose-700">Perbaikan</p>
            <h1 class="text-3xl font-semibold text-gray-950">Report asset damage</h1>
            <p class="mt-2 text-sm text-gray-600">Log a new issue for an asset so the repair queue can pick it up.</p>
        </div>

        <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.damage-reports.index') }}">
            Back to queue
        </a>
    </section>

    <form action="{{ route('web.damage-reports.store') }}" class="mt-8 space-y-6" method="POST">
        @csrf

        @include('damage-reports._form', [
            'damageReport' => $damageReport,
            'selectedAsset' => $selectedAsset,
            'assets' => $assets,
            'locations' => $locations,
            'users' => $users,
            'severityOptions' => $severityOptions,
            'statusOptions' => $statusOptions,
            'selectedLocationId' => $selectedLocationId,
            'blockedByMissingAssets' => $blockedByMissingAssets,
        ])

        @if (! $blockedByMissingAssets)
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap gap-3">
                    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Create damage report</button>
                    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.damage-reports.index') }}">Cancel</a>
                </div>
            </section>
        @endif
    </form>
@endsection
