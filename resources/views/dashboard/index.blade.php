@extends('layouts.app')

@section('title', 'Dashboard | Hospital Asset Manager')
@section('page-eyebrow', 'RS Mitra Husada')
@section('page-heading', 'Dashboard')

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.assets.index') }}">
        Browse Asset
    </a>
    <a class="rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" href="{{ route('web.assets.create') }}">
        Tambah Aset
    </a>
    
    <form action="{{ route('logout') }}" method="POST" class="inline-block ml-2">
        @csrf
        <button type="submit" class="rounded-full border border-red-200 bg-red-50 px-5 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-100 hover:text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Logout
        </button>
    </form>
@endsection

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.25fr_0.95fr]">
        <article class="rounded-[28px] bg-[#33457f] px-7 py-7 text-white shadow-[0_18px_50px_rgba(51,69,127,0.28)] lg:px-8 lg:py-8">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-white/65">Ringkasan Hari Ini</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-[24px] bg-white/10 p-5 ring-1 ring-white/10 backdrop-blur-sm">
                    <p class="text-sm font-medium text-white/70">Total Aset</p>
                    <p class="mt-3 text-3xl font-black">{{ number_format($totalAssets) }}</p>
                </article>

                <article class="rounded-[24px] bg-emerald-400/15 p-5 ring-1 ring-emerald-200/20 backdrop-blur-sm">
                    <p class="text-sm font-medium text-emerald-100">Tersedia</p>
                    <p class="mt-3 text-3xl font-black text-white">{{ number_format($statusCounts['available']) }}</p>
                </article>

                <article class="rounded-[24px] bg-sky-400/15 p-5 ring-1 ring-sky-200/20 backdrop-blur-sm">
                    <p class="text-sm font-medium text-sky-100">Sedang Dipakai</p>
                    <p class="mt-3 text-3xl font-black text-white">{{ number_format($statusCounts['in_use']) }}</p>
                </article>

                <article class="rounded-[24px] bg-amber-300/15 p-5 ring-1 ring-amber-100/20 backdrop-blur-sm">
                    <p class="text-sm font-medium text-amber-100">Perawatan</p>
                    <p class="mt-3 text-3xl font-black text-white">{{ number_format($statusCounts['maintenance']) }}</p>
                </article>
            </div>
        </article>

        <article class="rounded-[28px] bg-white p-7 shadow-sm ring-1 ring-black/5 lg:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Akses Cepat</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Modul Utama</h2>
                </div>
                <a class="text-sm font-semibold text-[#33457f] hover:text-[#25315a]" href="{{ route('web.asset-search.index') }}">
                    Buka pencarian
                </a>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <a class="rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-5 transition hover:border-slate-300 hover:bg-white" href="{{ route('web.asset-search.index') }}">
                    <p class="text-sm font-semibold text-slate-500">Pencarian Aset</p>
                    <p class="mt-2 text-lg font-bold text-slate-950">Cari aset dengan cepat</p>
                </a>

                <a class="rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-5 transition hover:border-slate-300 hover:bg-white" href="{{ route('web.damage-reports.index') }}">
                    <p class="text-sm font-semibold text-slate-500">Damage Reports</p>
                    <p class="mt-2 text-lg font-bold text-slate-950">Tinjau laporan aktif</p>
                </a>

                <a class="rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-5 transition hover:border-slate-300 hover:bg-white" href="{{ route('web.locations.index') }}">
                    <p class="text-sm font-semibold text-slate-500">Ruangan & Gedung</p>
                    <p class="mt-2 text-lg font-bold text-slate-950">Kelola data ruangan</p>
                </a>

                <a class="rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-5 transition hover:border-slate-300 hover:bg-white" href="{{ route('web.assets.index') }}">
                    <p class="text-sm font-semibold text-slate-500">Browse Asset</p>
                    <p class="mt-2 text-lg font-bold text-slate-950">Lihat inventaris lengkap</p>
                </a>
            </div>
        </article>
    </section>

    <section class="mt-6 rounded-[28px] bg-white shadow-sm ring-1 ring-black/5">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between lg:px-8">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Inventaris Terbaru</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Aset Terakhir yang Ditambahkan</h2>
            </div>
            <a class="text-sm font-semibold text-[#33457f] hover:text-[#25315a]" href="{{ route('web.assets.index') }}">
                Lihat semua aset
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/80 text-left text-slate-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold lg:px-8">Aset</th>
                        <th class="px-6 py-4 font-semibold">Kategori</th>
                        <th class="px-6 py-4 font-semibold">Ruangan</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($recentAssets as $asset)
                        <tr class="bg-white">
                            <td class="px-6 py-4 lg:px-8">
                                <a class="font-semibold text-slate-950 hover:text-[#33457f]" href="{{ route('web.assets.show', $asset) }}">{{ $asset->name }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $asset->asset_code }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ $asset->category?->name ?? 'Tanpa kategori' }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $asset->currentLocation?->name ?? 'Belum ditetapkan' }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ str($asset->status?->value ?? 'unknown')->headline() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-6 py-10 text-center text-slate-500 lg:px-8" colspan="4">Belum ada aset yang ditambahkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection