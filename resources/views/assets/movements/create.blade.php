@extends('layouts.app')

@section('title', 'Record Movement - '.$asset->name.' | Hospital Asset Manager')
@section('page-eyebrow', 'Form Pemindahan')
@section('page-heading')
    <span class="text-3xl lg:text-4xl">{{ $asset->name }}</span>
@endsection

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.assets.tracking.show', $asset) }}">Back to tracking</a>
@endsection

@section('content')
    <section class="rounded-[28px] border border-slate-200 bg-white px-6 py-5 shadow-sm">
        <p class="text-sm text-slate-600">{{ $asset->asset_code }}{{ $asset->category ? ' - '.$asset->category->name : '' }}</p>
    </section>

    <section class="mt-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-950">Penempatan Saat Ini</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Lokasi Sekarang</p>
                <p class="mt-1 text-sm text-gray-900">{{ $asset->currentLocation?->name ?? 'Unassigned' }}</p>
            </div>
        </div>
    </section>

    <form
        action="{{ route('web.assets.movements.store', $asset) }}"
        class="mt-6 grid gap-6 lg:grid-cols-2"
        method="POST"
    >
        @csrf

        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Form pemindahan</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-900" for="to_location_id">Lokasi Destinasi</label>
                        <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="to_location_id" name="to_location_id" data-location-select>
                            <option value="">Lokasi destinasi</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected((string) $selectedDestinationLocationId === (string) $location->id)>
                                    {{ $location->name }} ({{ $location->code }}){{ $location->floor_number !== null ? ' - Floor '.$location->floor_number : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('to_location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="moved_by_user_id">Moved by user</label>
                        <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="moved_by_user_id" name="moved_by_user_id">
                            <option value="">Leave unattributed</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((string) old('moved_by_user_id') === (string) $user->id)>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('moved_by_user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="reason">Reason</label>
                        <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="reason" name="reason" type="text" value="{{ old('reason') }}">
                        @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="moved_at">Moved at</label>
                        <input
                            class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                            id="moved_at"
                            name="moved_at"
                            required
                            type="datetime-local"
                            min="{{ \Illuminate\Support\Carbon::parse(\App\Http\Requests\StoreAssetMovementRequest::EARLIEST_MOVED_AT)->format('Y-m-d\TH:i') }}"
                            max="{{ \Illuminate\Support\Carbon::parse(\App\Http\Requests\StoreAssetMovementRequest::LATEST_MOVED_AT)->format('Y-m-d\TH:i') }}"
                            value="{{ old('moved_at') ? \Illuminate\Support\Carbon::parse(old('moved_at'))->format('Y-m-d\TH:i') : '' }}"
                        >
                        @error('moved_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-900" for="notes">Notes</label>
                        <textarea class="mt-2 block min-h-28 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" id="notes" name="notes">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Submission</h2>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Save movement</button>
                    <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.tracking.show', $asset) }}">Cancel</a>
                </div>
            </section>
        </div>
    </form>
@endsection
