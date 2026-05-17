@extends('layouts.app')

@section('title', 'Report Damage | Hospital Asset Manager')
@section('page-eyebrow', 'Perbaikan')
@section('page-heading', 'Report asset damage')

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.damage-reports.index') }}">
        Back to queue
    </a>
@endsection

@section('content')
    <form action="{{ route('web.damage-reports.store') }}" class="space-y-6" method="POST">
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
                    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Ajukan Laporan</button>
                    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.damage-reports.index') }}">Cancel</a>
                </div>
            </section>
        @endif
    </form>
@endsection
