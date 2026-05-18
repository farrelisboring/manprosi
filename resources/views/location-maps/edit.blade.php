@extends('layouts.app')

@section('title', 'Edit '.$locationMap->name.' | Hospital Asset Manager')
@section('page-eyebrow', 'Informasi Gedung')
@section('page-heading', 'Edit Gedung')

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.location-maps.show', $locationMap) }}">View map</a>
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.location-maps.index') }}">Back to maps</a>
@endsection

@section('content')
    <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('web.location-maps.update', $locationMap) }}" method="POST">
            @include('location-maps._form', [
                'locationMap' => $locationMap,
                'submitLabel' => 'Simpan Perubahan',
                'cancelUrl' => route('web.location-maps.show', $locationMap),
            ])
        </form>
    </section>
@endsection
