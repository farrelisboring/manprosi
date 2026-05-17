@extends('layouts.app')

@section('title', 'New Location Map | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-cyan-700">Informasi Gedung</p>
            <h1 class="text-3xl font-semibold text-gray-950">Tambahkan Gedung</h1>
        </div>

        <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.location-maps.index', $selectedLocationId ? ['location_id' => $selectedLocationId] : []) }}">Back to maps</a>
    </section>

    <section class="mt-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('web.location-maps.store') }}" method="POST">
            @include('location-maps._form', [
                'locationMap' => $locationMap,
                'submitLabel' => 'Create map',
                'cancelUrl' => route('web.location-maps.index', $selectedLocationId ? ['location_id' => $selectedLocationId] : []),
            ])
        </form>
    </section>
@endsection
