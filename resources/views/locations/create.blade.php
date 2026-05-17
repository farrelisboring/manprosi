@extends('layouts.app')

@section('title', 'New Location | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-amber-700">Informasi Ruangan</p>
            <h1 class="text-3xl font-semibold text-gray-950">Ruangan Baru</h1>
        </div>

        
    </section>

    <section class="mt-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('web.locations.store') }}" method="POST">
            @include('locations._form', [
                'location' => $location,
                'submitLabel' => 'Simpan Ruangan',
                'cancelUrl' => route('web.locations.index'),
            ])
        </form>
    </section>
@endsection
