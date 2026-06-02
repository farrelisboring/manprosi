@extends('layouts.app')

@section('title', 'New Location | Hospital Asset Manager')
@section('page-eyebrow', 'Informasi Ruangan')
@section('page-heading', 'Ruangan Baru')

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.locations.index') }}">Kembali ke Ruangan</a>
@endsection

@section('content')
    <section class="rounded-[28px] border border-slate-200 bg-white px-6 py-5 shadow-sm">
        <p class="text-sm text-slate-600">Tambahkan ruangan baru beserta gedung, lantai, status aktif, dan denah jika sudah tersedia.</p>
    </section>

    <form action="{{ route('web.locations.store') }}" enctype="multipart/form-data" method="POST">
        @include('locations._form', [
            'location' => $location,
            'submitLabel' => 'Simpan Ruangan',
            'cancelUrl' => route('web.locations.index'),
        ])
    </form>
@endsection
