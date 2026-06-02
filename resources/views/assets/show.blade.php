@extends('layouts.app')

@section('title', $asset->name.' | Hospital Asset Manager')
@section('page-eyebrow', 'Detail Aset')
@section('page-heading')
    <span class="text-3xl lg:text-4xl">{{ $asset->name }}</span>
@endsection

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.assets.index') }}">Kembali ke Aset</a>
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.assets.tracking.show', $asset) }}">Track Aset</a>
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.assets.geofence.show', $asset) }}">Notifikasi Geofence</a>
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.assets.movements.create', $asset) }}">Form Pemindahan Aset</a>
    <a class="rounded-full border border-rose-300 px-5 py-3 text-sm font-semibold text-rose-700 transition hover:border-rose-400 hover:text-rose-900" href="{{ route('web.damage-reports.create', ['asset_id' => $asset->id]) }}">Lapor Kerusakan</a>
    <a class="rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" href="{{ route('web.assets.edit', $asset) }}">Edit asset</a>
@endsection

@section('content')
    @php
        $shortLinkAppend = trim((string) config('app.short_link_append', ''));
        $normalizedShortLinkAppend = $shortLinkAppend !== '' ? rtrim($shortLinkAppend, '/') : null;
        $qrShortUrl = null;

        if ($asset->qr_code_value) {
            $qrShortUrl = $normalizedShortLinkAppend
                ? $normalizedShortLinkAppend.'/'.$asset->qr_code_value
                : route('web.qr-labels.redirect', $asset->qr_code_value);
        }

        $qrDownloadName = 'asset-'.str($asset->asset_code)->slug()->value().'-qr.png';
    @endphp

    <section class="rounded-[28px] border border-slate-200 bg-white px-6 py-5 shadow-sm">
        <p class="text-sm text-slate-600">{{ $asset->asset_code }}{{ $asset->category ? ' · '.$asset->category->name : '' }}</p>
    </section>

    <section class="mt-6 grid gap-8 xl:grid-cols-[1.6fr_1fr]">
        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Rekaman Aset</h2>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ str($asset->status?->value ?? 'unknown')->headline() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $asset->category?->name ?? 'Uncategorized' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Brand</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $asset->brand ?: 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Model</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $asset->model ?: 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Nomer Seri</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $asset->serial_number ?: 'Not set' }}</dd>
                    </div>
                </dl>

                <div class="mt-5 border-t border-gray-200 pt-5">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-700">{{ $asset->description ?: 'No description yet.' }}</dd>
                </div>

                <div class="mt-5 border-t border-gray-200 pt-5">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Notes</dt>
                    <dd class="mt-1 text-sm text-gray-700">{{ $asset->notes ?: 'No notes recorded.' }}</dd>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Lokasi</h2>
                <div class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                    <dl class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Ruangan Saat Ini</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $asset->currentLocation?->name ?? 'Unassigned' }}</dd>
                        </div>
                    </dl>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Denah Ruangan</p>
                        <div class="mt-3">
                            @if ($asset->currentLocation?->hasDenahImage())
                                <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                    <img
                                        alt="Denah {{ $asset->currentLocation->name }}"
                                        class="max-h-[24rem] w-full object-contain"
                                        src="{{ route('web.locations.denah', $asset->currentLocation) }}"
                                    >
                                </div>
                            @else
                                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-sm text-gray-600">
                                    Belum ada gambar denah untuk ruangan ini.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-950">QR label</h2>
                    </div>
                    <span class="rounded-full {{ $asset->qr_code_value ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }} px-2.5 py-1 text-xs font-medium">
                        {{ $asset->qr_code_value ? 'Assigned' : 'Missing' }}
                    </span>
                </div>

                <div
                    class="mt-5 grid gap-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 sm:grid-cols-[auto_1fr]"
                    data-qr-preview
                    data-qr-value="{{ $asset->qr_code_value ?? '' }}"
                    data-short-url="{{ $qrShortUrl ?? '' }}"
                    data-download-name="{{ $qrDownloadName }}"
                >
                    <div class="flex h-48 w-48 items-center justify-center rounded-lg border border-gray-200 bg-white shadow-sm" data-qr-canvas-host>
                        <div class="px-4 text-center text-sm text-gray-500" data-qr-empty-state>
                            {{ $asset->qr_code_value ? 'Rendering QR preview...' : 'Generate a QR label to preview the short-link code.' }}
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">QR code value</p>
                            <p class="mt-2 break-all text-sm text-gray-900">{{ $asset->qr_code_value ?: 'No QR label has been generated yet.' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Short-link URL</p>
                            <p class="mt-2 break-all text-sm text-gray-700">{{ $qrShortUrl ?: 'A short-link URL will appear after a QR label is generated.' }}</p>
                        </div>

                        <div class="pt-2">
                            <a
                                class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:text-gray-950"
                                data-qr-download-link
                                download="{{ $qrDownloadName }}"
                                href="{{ $qrShortUrl ? '#' : '' }}"
                            >
                                Download QR image
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    @if ($asset->qr_code_value)
                        <form action="{{ route('web.assets.qr-label.update', $asset) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input name="confirm_regeneration" type="hidden" value="1">
                            <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Regenerate QR</button>
                        </form>

                        <form action="{{ route('web.assets.qr-label.destroy', $asset) }}" method="POST" onsubmit="return confirm('Delete this QR label? Printed labels will become stale.');">
                            @csrf
                            @method('DELETE')
                            <input name="confirm_deletion" type="hidden" value="1">
                            <button class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:border-red-400 hover:text-red-900" type="submit">Delete QR</button>
                        </form>
                    @else
                        <form action="{{ route('web.assets.qr-label.store', $asset) }}" method="POST">
                            @csrf
                            <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Generate QR</button>
                        </form>
                    @endif
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Rekaman Tanggal</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Dibuat pada</dt>
                        <dd class="mt-1 text-gray-900">{{ $asset->created_at?->format('Y-m-d H:i') ?? 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Diperbarui pada</dt>
                        <dd class="mt-1 text-gray-900">{{ $asset->updated_at?->format('Y-m-d H:i') ?? 'Unknown' }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </section>
@endsection
