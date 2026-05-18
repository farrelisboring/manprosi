@extends('layouts.app')

@section('title', $location->name.' | Hospital Asset Manager')
@section('page-eyebrow', 'Detail Ruangan')
@section('page-heading')
    <span class="text-3xl lg:text-4xl">{{ $location->name }}</span>
@endsection

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.location-assets.index', ['location_id' => $location->id]) }}">Lihat Aset Ruangan</a>
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.location-maps.index') }}">Lihat Gedung</a>
    <a class="rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" href="{{ route('web.locations.edit', $location) }}">Edit location</a>
@endsection

@section('content')
    <section class="rounded-[28px] border border-slate-200 bg-white px-6 py-5 shadow-sm">
        <p class="text-sm text-slate-600">{{ $location->code }} - {{ $location->type }}{{ $location->floor_number !== null ? ' - Lantai '.$location->floor_number : '' }}</p>
    </section>

    <section class="mt-6 grid gap-8 xl:grid-cols-[1.4fr_1fr]">
        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Ringkasan Ruangan</h2>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Room/location code</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $location->code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $location->type }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Gedung</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if ($location->locationMap)
                                <a class="hover:text-cyan-800" href="{{ route('web.location-maps.show', $location->locationMap) }}">{{ $location->locationMap->name }}</a>
                            @else
                                Belum dipilih
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Floor</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $location->floor_number !== null ? 'Floor '.$location->floor_number : 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">State</dt>
                        <dd class="mt-1">
                            <span class="rounded-full {{ $location->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }} px-2.5 py-1 text-xs font-medium">
                                {{ $location->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                </dl>

                <div class="mt-5 border-t border-gray-200 pt-5">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-700">{{ $location->description ?: 'No description recorded.' }}</dd>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-950">Gedung Terkait</h2>
                    <a class="text-sm font-medium text-amber-800 hover:text-amber-900" href="{{ route('web.location-maps.index') }}">Kelola Gedung</a>
                </div>

                <div class="mt-5 space-y-3">
                    @if ($location->locationMap)
                        <div class="rounded-lg border border-gray-200 px-4 py-3">
                            <a class="font-medium text-gray-950 hover:text-cyan-800" href="{{ route('web.location-maps.show', $location->locationMap) }}">{{ $location->locationMap->name }}</a>
                            <p class="mt-1 text-sm text-gray-600">{{ $location->locationMap->notes ?: 'No notes recorded.' }}</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">Ruangan ini belum terhubung ke gedung mana pun.</p>
                    @endif
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Record actions</h2>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.locations.index') }}">Kembali ke Ruangan</a>

                    @if ($isDeletionBlocked)
                        <button
                            class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:border-red-400 hover:text-red-900"
                            data-blocked-action-message="{{ $blockedDeletionMessage }}"
                            type="button"
                        >
                            Hapus Ruangan
                        </button>
                    @else
                        <form action="{{ route('web.locations.destroy', $location) }}" method="POST" onsubmit="return confirm('Delete this location?');">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:border-red-400 hover:text-red-900" type="submit">Delete location</button>
                        </form>
                    @endif
                </div>
            </section>
        </div>
    </section>
@endsection
