@extends('layouts.app')

@section('title', 'Notifikasi Geofence - '.$asset->name.' | Hospital Asset Manager')
@section('page-eyebrow', 'Notifikasi Geofence')
@section('page-heading')
    <span class="text-3xl lg:text-4xl">{{ $asset->name }}</span>
@endsection

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.assets.show', $asset) }}">Detail Aset</a>
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.assets.tracking.show', $asset) }}">Tracking Aset</a>
@endsection

@section('content')
    <section class="rounded-[28px] border border-slate-200 bg-white px-6 py-5 shadow-sm">
        <p class="text-sm text-slate-600">{{ $asset->asset_code }}{{ $asset->category ? ' - '.$asset->category->name : '' }}</p>
    </section>

    <section class="mt-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-950">Notifikasi Geofence</h2>
            </div>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Tanggal</th>
                        <th class="px-4 py-3 font-medium">Ruangan</th>
                        <th class="px-4 py-3 font-medium">Pesan</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($alerts as $alert)
                        <tr class="bg-white align-top">
                            <td class="px-4 py-4">
                                <p class="font-medium text-gray-950">{{ $alert->triggered_at?->format('Y-m-d H:i') ?? 'Unknown' }}</p>
                            </td>
                            <td class="px-4 py-4 text-gray-700">
                                {{ $alert->location?->name ?? 'Ruangan tidak tersedia' }}
                            </td>
                            <td class="px-4 py-4 text-gray-700">{{ $alert->message }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ str($alert->status?->value ?? 'unknown')->headline() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-10 text-center text-gray-600" colspan="4">Belum ada notifikasi geofence untuk aset ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 border-t border-gray-200 pt-4">
            {{ $alerts->links() }}
        </div>
    </section>
@endsection
