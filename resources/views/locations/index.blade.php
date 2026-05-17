@extends('layouts.app')

@section('title', 'Locations | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-amber-700">Informasi Lokasi</p>
            <h1 class="text-3xl font-semibold text-gray-950">Lokasi</h1>
        </div>

        <div class="flex flex-wrap gap-3">
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-assets.index') }}">Lihat Aset Lokasi</a>
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.index') }}">Tambah Gedung</a>
            <a class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" href="{{ route('web.locations.create') }}">Lokasi Baru</a>
        </div>
    </section>

    <section class="mt-8 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Lokasi</th>
                        <th class="px-4 py-3 font-medium">Gedung</th>
                        <th class="px-4 py-3 font-medium">TODO: DELETE</th>
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
                                @if ($location->parent)
                                    <a class="hover:text-amber-800" href="{{ route('web.locations.show', $location->parent) }}">{{ $location->parent->name }}</a>
                                @else
                                    No parent
                                @endif
                            </td>
                            <td class="px-4 py-4 text-gray-700">{{ $location->type }}</td>
                            <td class="px-4 py-4 text-gray-700">{{ $location->floor_number !== null ? 'Floor '.$location->floor_number : 'Not set' }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full {{ $location->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }} px-2.5 py-1 text-xs font-medium">
                                    {{ $location->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.locations.show', $location) }}">View</a>
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.locations.edit', $location) }}">Edit</a>
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.index', ['location_id' => $location->id]) }}">Maps</a>

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
                            <td class="px-4 py-10 text-center text-gray-600" colspan="6">No locations have been created yet.</td>
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
