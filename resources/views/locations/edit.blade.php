@extends('layouts.app')

@section('title', 'Edit '.$location->name.' | Hospital Asset Manager')
@section('page-eyebrow', 'Reference data')
@section('page-heading', 'Edit location')

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.locations.show', $location) }}">View location</a>
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.locations.index') }}">Back to locations</a>
@endsection

@section('content')
    <section class="rounded-[28px] border border-slate-200 bg-white px-6 py-5 shadow-sm">
        <p class="text-sm text-slate-600">Adjust this location's naming, hierarchy, and active state without leaving the Blade-first admin flow.</p>
    </section>

    <section class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('web.locations.update', $location) }}" method="POST">
            @include('locations._form', [
                'location' => $location,
                'submitLabel' => 'Simpan Perubahan',
                'cancelUrl' => route('web.locations.show', $location),
            ])
        </form>
    </section>
@endsection
