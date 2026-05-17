<div class="space-y-6" data-tracking-panel>
    <section class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Lokasi Sekarang</p>
            <p class="mt-3 text-lg font-semibold text-gray-950">{{ $asset->currentLocation?->name ?? 'Unassigned' }}</p>
            <p class="mt-1 text-sm text-gray-600">{{ $asset->currentLocation?->code ?? 'No active location' }}</p>
        </article>

        <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Gedung</p>
            <p class="mt-3 text-lg font-semibold text-gray-950">{{ $asset->currentLocation?->locationMap?->name ?? 'Belum terhubung ke gedung' }}</p>
        </article>

        <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Tanggal Perpindahan Terbaru</p>
            <p class="mt-3 text-lg font-semibold text-gray-950">{{ $latestMovement?->moved_at?->format('Y-m-d H:i') ?? 'No history yet' }}</p>
            <p class="mt-1 text-sm text-gray-600">{{ $latestMovement?->toLocation?->name ?? 'No destination recorded' }}</p>
        </article>
    </section>

    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-950">Riwayat Perpindahan</h2>
            </div>
        
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Tanggal</th>
                        <th class="px-4 py-3 font-medium">Rute</th>
                        <th class="px-4 py-3 font-medium">Pemohon</th>
                        <th class="px-4 py-3 font-medium">Alasan</th>
                        <th class="px-4 py-3 font-medium">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($movements as $movement)
                        <tr class="bg-white align-top">
                            <td class="px-4 py-4">
                                <p class="font-medium text-gray-950">{{ $movement->moved_at?->format('Y-m-d H:i') ?? 'Unknown' }}</p>
                                <p class="mt-1 text-xs text-gray-500">Recorded {{ $movement->created_at?->format('Y-m-d H:i') ?? 'Unknown' }}</p>
                            </td>
                            <td class="px-4 py-4 text-gray-700">
                                <p>{{ $movement->fromLocation?->name ?? 'No prior location' }}</p>
                                <p class="mt-1 text-xs text-gray-500">to {{ $movement->toLocation?->name ?? 'Unknown destination' }}</p>
                            </td>
                            <td class="px-4 py-4 text-gray-700">{{ $movement->movedByUser?->name ?? 'Not attributed' }}</td>
                            <td class="px-4 py-4 text-gray-700">{{ $movement->reason ?: 'No reason recorded' }}</td>
                            <td class="px-4 py-4 text-gray-700">{{ $movement->notes ?: 'No notes recorded' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-10 text-center text-gray-600" colspan="5">No movement history has been recorded for this asset yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 border-t border-gray-200 pt-4">
            {{ $movements->links() }}
        </div>
    </section>
</div>
