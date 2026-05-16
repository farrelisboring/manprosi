@extends('layouts.app')

@section('title', $asset->name.' | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-sm font-medium text-sky-700">Asset detail</p>
            <h1 class="text-3xl font-semibold text-gray-950">{{ $asset->name }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $asset->asset_code }}{{ $asset->category ? ' · '.$asset->category->name : '' }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.index') }}">Back to assets</a>
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.tracking.show', $asset) }}">Track asset</a>
            <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.movements.create', $asset) }}">Record movement</a>
            <a class="rounded-md border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700 hover:border-rose-400 hover:text-rose-900" href="{{ route('web.damage-reports.create', ['asset_id' => $asset->id]) }}">Report damage</a>
            <a class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" href="{{ route('web.assets.edit', $asset) }}">Edit asset</a>
        </div>
    </section>

    <section class="mt-8 grid gap-8 xl:grid-cols-[1.6fr_1fr]">
        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Asset snapshot</h2>
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
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Serial number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $asset->serial_number ?: 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">RFID tag</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $asset->rfid_tag ?: 'Not set' }}</dd>
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
                <h2 class="text-lg font-semibold text-gray-950">Placement and identifiers</h2>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Current location</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $asset->currentLocation?->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Current map</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $asset->currentMap?->name ?? 'No map placement' }}</dd>
                    </div>
                </dl>
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

                <div class="mt-5 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">QR code value</p>
                    <p class="mt-2 break-all text-sm text-gray-900">{{ $asset->qr_code_value ?: 'No QR label has been generated yet.' }}</p>
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
                <h2 class="text-lg font-semibold text-gray-950">Record metadata</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Created at</dt>
                        <dd class="mt-1 text-gray-900">{{ $asset->created_at?->format('Y-m-d H:i') ?? 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Updated at</dt>
                        <dd class="mt-1 text-gray-900">{{ $asset->updated_at?->format('Y-m-d H:i') ?? 'Unknown' }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </section>
@endsection
