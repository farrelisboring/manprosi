@extends('layouts.app')

@section('title', 'Location Maps | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-cyan-700">Informasi Gedung</p>
            <h1 class="text-3xl font-semibold text-gray-950">Gedung</h1>
        </div>

        <div class="flex flex-wrap gap-3">
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.locations.index') }}">Kembali ke Ruangan</a>
            <a class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" href="{{ route('web.location-maps.create') }}">Tambah Gedung</a>
        </div>
    </section>

    <section class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama Gedung</th>
                        <th class="px-4 py-3 font-medium">Jumlah Ruangan</th>
                        <th class="px-4 py-3 font-medium">Notes</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($maps as $map)
                        <tr class="bg-white align-top">
                            <td class="px-4 py-4">
                                <a class="font-medium text-gray-950 hover:text-cyan-800" href="{{ route('web.location-maps.show', $map) }}">{{ $map->name }}</a>
                            </td>
                            <td class="px-4 py-4 text-gray-700">{{ $map->locations_count }}</td>
                            <td class="px-4 py-4 text-gray-700">{{ $map->notes ?: 'No notes recorded.' }}</td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.show', $map) }}">View</a>
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.edit', $map) }}">Edit</a>

                                    @if (in_array($map->id, $blockedDeletionIds, true))
                                        <button
                                            class="rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-700 hover:border-red-400 hover:text-red-900"
                                            data-blocked-action-message="This item cannot be deleted because related records still exist."
                                            type="button"
                                        >
                                            Delete
                                        </button>
                                    @else
                                        <form action="{{ route('web.location-maps.destroy', $map) }}" method="POST" onsubmit="return confirm('Delete this location map?');">
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
                            <td class="px-4 py-10 text-center text-gray-600" colspan="4">No location maps have been created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-4 py-4">
            {{ $maps->links() }}
        </div>
    </section>
@endsection
