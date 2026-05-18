@extends('layouts.app')

@section('title', $damageReport->title.' | Hospital Asset Manager')
@section('page-eyebrow', 'Maintenance workflow')
@section('page-heading')
    <span class="text-3xl lg:text-4xl">{{ $damageReport->title }}</span>
@endsection

@section('page-actions')
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.damage-reports.index') }}">Back to queue</a>
    <a class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-950" href="{{ route('web.damage-reports.edit', $damageReport) }}">Edit report</a>
    <form action="{{ route('web.damage-reports.destroy', $damageReport) }}" method="POST" onsubmit="return confirm('Delete this damage report and its repair history?');">
        @csrf
        @method('DELETE')
        <button class="rounded-full border border-red-300 px-5 py-3 text-sm font-semibold text-red-700 transition hover:border-red-400 hover:text-red-900" type="submit">Delete report</button>
    </form>
@endsection

@section('content')
    <section class="rounded-[28px] border border-slate-200 bg-white px-6 py-5 shadow-sm">
        <p class="text-sm text-slate-600">
            {{ $damageReport->asset?->asset_code ?? 'No asset code' }} -
            {{ $damageReport->asset?->name ?? 'Unknown asset' }}
        </p>
    </section>

    <section class="mt-6 grid gap-8 xl:grid-cols-[1.5fr_1fr]">
        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Report snapshot</h2>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Severity</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ str($damageReport->severity?->value ?? 'unknown')->headline() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ str($damageReport->status?->value ?? 'unknown')->headline() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Reported at</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $damageReport->reported_at?->format('Y-m-d H:i') ?? 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Resolved at</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $damageReport->resolved_at?->format('Y-m-d H:i') ?? 'Not resolved' }}</dd>
                    </div>
                </dl>

                <div class="mt-5 border-t border-gray-200 pt-5">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-700">{{ $damageReport->description }}</dd>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Asset context</h2>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Asset</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $damageReport->asset?->name ?? 'Unknown asset' }}</dd>
                        <p class="mt-1 text-xs text-gray-500">{{ $damageReport->asset?->asset_code ?? 'No asset code' }}</p>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $damageReport->asset?->category?->name ?? 'Uncategorized' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Current asset location</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $damageReport->asset?->currentLocation?->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Reported location</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $damageReport->location?->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Reporter</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $damageReport->reportedByUser?->name ?? 'Not attributed' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Asset actions</dt>
                        <dd class="mt-2">
                            @if ($damageReport->asset)
                                <div class="flex flex-wrap gap-2">
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.show', $damageReport->asset) }}">View asset</a>
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.assets.tracking.show', $damageReport->asset) }}">Asset tracking</a>
                                </div>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-950">Repair timeline</h2>
                        <p class="text-sm text-gray-600">Append-only history of repair progress for this report.</p>
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    @forelse ($damageReport->repairUpdates as $repairUpdate)
                        <article class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-950">{{ $repairUpdate->result_summary ?: 'Repair update logged' }}</p>
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ str($repairUpdate->update_type?->value ?? 'note')->headline() }} -
                                        {{ $repairUpdate->updatedByUser?->name ?? 'Not attributed' }}
                                    </p>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <p>{{ $repairUpdate->logged_at?->format('Y-m-d H:i') ?? 'Unknown' }}</p>
                                    <p class="mt-1">{{ $repairUpdate->status_after ? 'Status after: '.str($repairUpdate->status_after->value)->headline() : 'Status unchanged' }}</p>
                                </div>
                            </div>

                            <p class="mt-3 text-sm text-gray-700">{{ $repairUpdate->notes ?: 'No notes recorded.' }}</p>
                        </article>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-center text-sm text-gray-600">
                            No repair updates have been logged yet.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm" id="repair-update-form">
                <h2 class="text-lg font-semibold text-gray-950">Log repair update</h2>
                <p class="mt-1 text-sm text-gray-600">Add a timeline entry and optionally move the report status forward.</p>

                <form action="{{ route('web.damage-reports.repair-updates.store', $damageReport) }}" class="mt-5 space-y-4" method="POST">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="updated_by_user_id">Updated by user</label>
                        <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="updated_by_user_id" name="updated_by_user_id">
                            <option value="">Leave unattributed</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((string) old('updated_by_user_id') === (string) $user->id)>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">TODO: Default this to the authenticated current user once auth exists (Show).</p>
                        @error('updated_by_user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="update_type">Update type</label>
                        <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="update_type" name="update_type">
                            @foreach ($repairUpdateTypes as $type)
                                <option value="{{ $type['value'] }}" @selected(old('update_type', 'note') === $type['value'])>{{ $type['value'] === 'note' ? 'Others' : $type['label'] }}</option>
                            @endforeach
                        </select>
                        @error('update_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="status_after">Status after</label>
                        <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="status_after" name="status_after">
                            <option value="">Keep current status</option>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status->value }}" @selected(old('status_after') === $status->value)>{{ str($status->value)->headline() }}</option>
                            @endforeach
                        </select>
                        @error('status_after') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="result_summary">Result summary</label>
                        <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="result_summary" name="result_summary" required type="text" value="{{ old('result_summary') }}">
                        @error('result_summary') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="logged_at">Logged at</label>
                        <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="logged_at" name="logged_at" required type="datetime-local" value="{{ old('logged_at') ? \Illuminate\Support\Carbon::parse(old('logged_at'))->format('Y-m-d\TH:i') : '' }}">
                        @error('logged_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900" for="notes">Notes</label>
                        <textarea class="mt-2 block min-h-28 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="notes" name="notes" required>{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Log repair update</button>
                </form>
            </section>
        </div>
    </section>
@endsection
