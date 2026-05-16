@php
    $currentLocation = $selectedAsset?->currentLocation ?? $damageReport?->location;
@endphp

@if ($blockedByMissingAssets)
    <section class="rounded-lg border border-amber-200 bg-amber-50 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-amber-950">Damage reporting is blocked</h2>
        <p class="mt-2 text-sm text-amber-900">Create at least one asset record before logging maintenance issues.</p>
    </section>
@else
    @if ($selectedAsset)
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Selected asset</h2>
            <p class="mt-2 text-sm text-gray-700">{{ $selectedAsset->name }} ({{ $selectedAsset->asset_code }})</p>
            <p class="mt-1 text-sm text-gray-600">
                {{ $selectedAsset->category?->name ?? 'Uncategorized' }} -
                {{ $selectedAsset->currentLocation?->name ?? 'No current location' }}
            </p>
        </section>
    @endif

    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-900" for="asset_id">Asset</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="asset_id" name="asset_id">
                    <option value="">Choose an asset</option>
                    @foreach ($assets as $assetOption)
                        <option value="{{ $assetOption->id }}" @selected((string) old('asset_id', $damageReport?->asset_id ?? $selectedAsset?->id) === (string) $assetOption->id)>
                            {{ $assetOption->name }} ({{ $assetOption->asset_code }})
                        </option>
                    @endforeach
                </select>
                @error('asset_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="reported_by_user_id">Reported by user</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="reported_by_user_id" name="reported_by_user_id">
                    <option value="">Leave unattributed</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('reported_by_user_id', $damageReport?->reported_by_user_id) === (string) $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">TODO: Default this to the authenticated current user once auth exists.</p>
                @error('reported_by_user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="location_id">Location</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="location_id" name="location_id">
                    <option value="">Use asset current location</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected((string) old('location_id', $selectedLocationId) === (string) $location->id)>
                            {{ $location->name }} ({{ $location->code }}){{ $location->floor_number !== null ? ' - Floor '.$location->floor_number : '' }}
                        </option>
                    @endforeach
                </select>
                @if ($currentLocation)
                    <p class="mt-1 text-xs text-gray-500">Current asset location: {{ $currentLocation->name }}</p>
                @endif
                @error('location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-900" for="title">Title</label>
                <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="title" name="title" type="text" value="{{ old('title', $damageReport?->title) }}">
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="severity">Severity</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="severity" name="severity">
                    @foreach ($severityOptions as $severity)
                        <option value="{{ $severity->value }}" @selected(old('severity', $damageReport?->severity?->value ?? \App\Enums\DamageSeverity::Medium->value) === $severity->value)>
                            {{ str($severity->value)->headline() }}
                        </option>
                    @endforeach
                </select>
                @error('severity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="status">Status</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="status" name="status">
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $damageReport?->status?->value ?? \App\Enums\DamageStatus::Reported->value) === $status->value)>
                            {{ str($status->value)->headline() }}
                        </option>
                    @endforeach
                </select>
                @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="reported_at">Reported at</label>
                <input
                    class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    id="reported_at"
                    name="reported_at"
                    type="datetime-local"
                    value="{{ old('reported_at', $damageReport?->reported_at?->format('Y-m-d\TH:i')) }}"
                >
                @error('reported_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="resolved_at">Resolved at</label>
                <input
                    class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200"
                    id="resolved_at"
                    name="resolved_at"
                    type="datetime-local"
                    value="{{ old('resolved_at', $damageReport?->resolved_at?->format('Y-m-d\TH:i')) }}"
                >
                <p class="mt-1 text-xs text-gray-500">Jika tidak diisi, laporan akan berstatus unresolved.</p>
                @error('resolved_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-900" for="description">Description</label>
                <textarea class="mt-2 block min-h-36 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="description" name="description">{{ old('description', $damageReport?->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>
@endif
