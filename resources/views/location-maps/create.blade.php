@extends('layouts.app')

@section('title', 'New Location Map | Hospital Asset Manager')
@section('page-eyebrow', 'Informasi Gedung')
@section('page-heading', 'Tambahkan Gedung')

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.location-maps.index') }}">Kembali ke Gedung</a>
@endsection

@section('content')
    <section class="rounded-[28px] border border-slate-200 bg-white px-6 py-5 shadow-sm">
        <p class="text-sm text-slate-600">Simpan data gedung utama agar ruangan bisa ditautkan ke lokasi bangunan yang tepat.</p>
    </section>

    <form action="{{ route('web.location-maps.store') }}" method="POST">
        @include('location-maps._form', [
            'locationMap' => $locationMap,
            'submitLabel' => 'Simpan Gedung',
            'cancelUrl' => route('web.location-maps.index'),
        ])
    </form>
@endsection
