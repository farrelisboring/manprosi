@extends('layouts.app')

@section('title', 'Edit Damage Report | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-sm font-medium text-rose-700">Maintenance workflow</p>
            <h1 class="text-3xl font-semibold text-gray-950">Edit damage report</h1>
            <p class="mt-2 text-sm text-gray-600">Update the report details while keeping repair history on the report timeline.</p>
        </div>

        <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.damage-reports.show', $damageReport) }}">
            Back to report
        </a>
    </section>

    <form action="{{ route('web.damage-reports.update', $damageReport) }}" class="mt-8 space-y-6" method="POST">
        @csrf
        @method('PATCH')

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
                    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Save changes</button>
                    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.damage-reports.show', $damageReport) }}">Cancel</a>
                </div>
            </section>
        @endif
    </form>
@endsection
