@extends('layouts.app')

@section('title', 'Locations | Hospital Asset Manager')
@section('page-eyebrow', 'Data Referensi')
@section('page-heading', 'Ruangan & Gedung')

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.location-assets.index') }}">
        Aset per Ruangan
    </a>
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.location-maps.index') }}">
        Kelola Gedung
    </a>
    <a class="rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" href="{{ route('web.locations.create') }}">
        Ruangan Baru
    </a>
@endsection

@section('content')
    <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama Ruangan</th>
                        <th class="px-4 py-3 font-medium">Gedung</th>
                        <th class="px-4 py-3 font-medium">Lantai</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($locations as $location)
                        <tr class="bg-white align-top">
                            <td class="px-4 py-4">
                                <a class="font-medium text-gray-950 hover:text-amber-800" href="{{ route('web.locations.show', $location) }}">{{ $location->name }}</a>
                                <p class="mt-1 text-xs text-gray-500">{{ $location->code }}</p>
                            </td>
                            <td class="px-4 py-4 text-gray-700">
                                @if ($location->locationMap)
                                    <a class="hover:text-cyan-800" href="{{ route('web.location-maps.show', $location->locationMap) }}">{{ $location->locationMap->name }}</a>
                                @else
                                    Belum dipilih
                                @endif
                            </td>
                            <td class="px-4 py-4 text-gray-700">{{ $location->floor_number !== null ? 'Lantai '.$location->floor_number : 'Belum diatur' }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full {{ $location->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }} px-2.5 py-1 text-xs font-medium">
                                    {{ $location->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.locations.show', $location) }}">View</a>
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.locations.edit', $location) }}">Edit</a>
                                    @if ($location->locationMap)
                                        <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.show', $location->locationMap) }}">Gedung</a>
                                    @endif

                                    @if (in_array($location->id, $blockedDeletionIds, true))
                                        <button
                                            class="rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-700 hover:border-red-400 hover:text-red-900"
                                            data-blocked-action-message="This item cannot be deleted because related records still exist."
                                            type="button"
                                        >
                                            Delete
                                        </button>
                                    @else
                                        <form action="{{ route('web.locations.destroy', $location) }}" method="POST" onsubmit="return confirm('Delete this location?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-700 hover:border-red-400 hover:text-red-900" type="submit">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-10 text-center text-gray-600" colspan="5">No locations have been created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-4 py-4">
            {{ $locations->links() }}
        </div>
    </section>
@endsection
