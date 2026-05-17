@extends('layouts.app')

@section('title', 'Edit '.$location->name.' | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-amber-700">Reference data</p>
            <h1 class="text-3xl font-semibold text-gray-950">Edit location</h1>
            <p class="mt-2 max-w-3xl text-sm text-gray-600">Adjust this location’s naming, hierarchy, and active state without leaving the Blade-first admin flow.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.locations.show', $location) }}">View location</a>
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.locations.index') }}">Back to locations</a>
        </div>
    </section>

    <section class="mt-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('web.locations.update', $location) }}" method="POST">
            @include('locations._form', [
                'location' => $location,
                'submitLabel' => 'Simpan Perubahan',
                'cancelUrl' => route('web.locations.show', $location),
            ])
        </form>
    </section>
@endsection
