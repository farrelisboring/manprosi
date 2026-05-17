@extends('layouts.app')

@section('title', $location->name.' | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-sm font-medium text-amber-700">Location detail</p>
            <h1 class="text-3xl font-semibold text-gray-950">{{ $location->name }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $location->code }} · {{ $location->type }}{{ $location->floor_number !== null ? ' · Floor '.$location->floor_number : '' }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-assets.index', ['location_id' => $location->id]) }}">View assigned assets</a>
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.index', ['location_id' => $location->id]) }}">View maps</a>
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.create', ['location_id' => $location->id]) }}">Add map</a>
            <a class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" href="{{ route('web.locations.edit', $location) }}">Edit location</a>
        </div>
    </section>

    <section class="mt-8 grid gap-8 xl:grid-cols-[1.4fr_1fr]">
        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Location snapshot</h2>
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
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Parent location</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if ($location->parent)
                                <a class="hover:text-amber-800" href="{{ route('web.locations.show', $location->parent) }}">{{ $location->parent->name }}</a>
                            @else
                                No parent location
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

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-950">Child locations</h2>
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ $location->children->count() }}</span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($location->children as $child)
                        <div class="rounded-lg border border-gray-200 px-4 py-3">
                            <a class="font-medium text-gray-950 hover:text-amber-800" href="{{ route('web.locations.show', $child) }}">{{ $child->name }}</a>
                            <p class="mt-1 text-sm text-gray-600">{{ $child->code }} · {{ $child->type }}{{ $child->floor_number !== null ? ' · Floor '.$child->floor_number : '' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">No child locations are attached to this record.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-950">Location maps</h2>
                    <a class="text-sm font-medium text-amber-800 hover:text-amber-900" href="{{ route('web.location-maps.create', ['location_id' => $location->id]) }}">Add map</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($location->maps as $map)
                        <div class="rounded-lg border border-gray-200 px-4 py-3">
                            <a class="font-medium text-gray-950 hover:text-cyan-800" href="{{ route('web.location-maps.show', $map) }}">{{ $map->name }}</a>
                            <p class="mt-1 text-sm text-gray-600">{{ $map->notes ?: 'No notes recorded.' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">No location maps have been created for this location yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Record actions</h2>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.locations.index') }}">Back to locations</a>

                    @if ($isDeletionBlocked)
                        <button
                            class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:border-red-400 hover:text-red-900"
                            data-blocked-action-message="{{ $blockedDeletionMessage }}"
                            type="button"
                        >
                            Delete location
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
