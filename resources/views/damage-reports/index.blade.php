@extends('layouts.app')

@section('title', 'Damage and Repair Queue | Hospital Asset Manager')

@section('content')
    <section class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-rose-700">Maintenance workflow</p>
            <h1 class="text-3xl font-semibold text-gray-950">Damage and repair queue</h1>
            <p class="mt-2 max-w-3xl text-sm text-gray-600">Track reported issues, move repairs forward, and keep the maintenance history attached to each asset.</p>
        </div>

        <a class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" href="{{ route('web.damage-reports.create') }}">
            New damage report
        </a>
    </section>

    <section class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <a class="rounded-lg border border-rose-200 bg-white p-5 shadow-sm hover:border-rose-300" data-status-count="reported" href="{{ route('web.damage-reports.index', array_filter(array_merge(request()->except('page', 'status'), ['status' => \App\Enums\DamageStatus::Reported->value]))) }}">
            <p class="text-sm font-medium text-rose-700">Reported</p>
            <p class="mt-3 text-3xl font-semibold text-rose-900">{{ number_format($summaryCounts[\App\Enums\DamageStatus::Reported->value]) }}</p>
            <p class="mt-2 text-sm text-gray-600">{{ number_format($summaryCounts[\App\Enums\DamageStatus::Reported->value]) }} reported</p>
        </a>

        <a class="rounded-lg border border-amber-200 bg-white p-5 shadow-sm hover:border-amber-300" data-status-count="in_progress" href="{{ route('web.damage-reports.index', array_filter(array_merge(request()->except('page', 'status'), ['status' => \App\Enums\DamageStatus::InProgress->value]))) }}">
            <p class="text-sm font-medium text-amber-700">In progress</p>
            <p class="mt-3 text-3xl font-semibold text-amber-900">{{ number_format($summaryCounts[\App\Enums\DamageStatus::InProgress->value]) }}</p>
            <p class="mt-2 text-sm text-gray-600">{{ number_format($summaryCounts[\App\Enums\DamageStatus::InProgress->value]) }} in progress</p>
        </a>

        <a class="rounded-lg border border-emerald-200 bg-white p-5 shadow-sm hover:border-emerald-300" data-status-count="resolved" href="{{ route('web.damage-reports.index', array_filter(array_merge(request()->except('page', 'status'), ['status' => \App\Enums\DamageStatus::Resolved->value]))) }}">
            <p class="text-sm font-medium text-emerald-700">Resolved</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-900">{{ number_format($summaryCounts[\App\Enums\DamageStatus::Resolved->value]) }}</p>
            <p class="mt-2 text-sm text-gray-600">{{ number_format($summaryCounts[\App\Enums\DamageStatus::Resolved->value]) }} resolved</p>
        </a>
    </section>

    <section class="mt-8 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <form action="{{ route('web.damage-reports.index') }}" class="grid gap-4 lg:grid-cols-[1.2fr_1fr_1fr_1fr_1fr_1fr_1fr_auto] lg:items-end">
            <div>
                <label class="block text-sm font-medium text-gray-900" for="asset_id">Asset</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="asset_id" name="asset_id">
                    <option value="">All assets</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}" @selected((string) request('asset_id') === (string) $asset->id)>
                            {{ $asset->name }} ({{ $asset->asset_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="location_id">Location</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="location_id" name="location_id">
                    <option value="">All locations</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected((string) request('location_id') === (string) $location->id)>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="reported_by_user_id">Reporter</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="reported_by_user_id" name="reported_by_user_id">
                    <option value="">Any reporter</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) request('reported_by_user_id') === (string) $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="severity">Severity</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="severity" name="severity">
                    <option value="">All severities</option>
                    @foreach ($severityOptions as $severity)
                        <option value="{{ $severity->value }}" @selected(request('severity') === $severity->value)>
                            {{ str($severity->value)->headline() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="status">Status</label>
                <select class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="status" name="status">
                    <option value="">Open reports</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status->value }}" @selected($selectedStatusFilter === $status->value)>
                            {{ str($status->value)->headline() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="date_from">Reported from</label>
                <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="date_from" name="date_from" type="datetime-local" value="{{ request('date_from') ? \Illuminate\Support\Carbon::parse(request('date_from'))->format('Y-m-d\TH:i') : '' }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900" for="date_to">Reported to</label>
                <input class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200" id="date_to" name="date_to" type="datetime-local" value="{{ request('date_to') ? \Illuminate\Support\Carbon::parse(request('date_to'))->format('Y-m-d\TH:i') : '' }}">
            </div>

            <div class="flex items-end gap-3">
                <button class="rounded-md bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" type="submit">Apply</button>
                <a class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.damage-reports.index') }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Report</th>
                        <th class="px-4 py-3 font-medium">Asset</th>
                        <th class="px-4 py-3 font-medium">Location</th>
                        <th class="px-4 py-3 font-medium">Severity</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Reported</th>
                        <th class="px-4 py-3 font-medium">Reporter</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($reports as $report)
                        <tr class="bg-white align-top">
                            <td class="px-4 py-4">
                                <a class="font-medium text-gray-950 hover:text-rose-800" href="{{ route('web.damage-reports.show', $report) }}">{{ $report->title }}</a>
                                <p class="mt-1 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($report->description, 90) }}</p>
                            </td>
                            <td class="px-4 py-4 text-gray-700">
                                <p>{{ $report->asset?->name ?? 'Unknown asset' }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $report->asset?->asset_code ?? 'No asset code' }}</p>
                            </td>
                            <td class="px-4 py-4 text-gray-700">{{ $report->location?->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ str($report->severity?->value ?? 'unknown')->headline() }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ str($report->status?->value ?? 'unknown')->headline() }}</span>
                            </td>
                            <td class="px-4 py-4 text-gray-700">
                                <p>{{ $report->reported_at?->format('Y-m-d H:i') ?? 'Unknown' }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $report->resolved_at ? 'Resolved '.$report->resolved_at->format('Y-m-d H:i') : 'Still open' }}</p>
                            </td>
                            <td class="px-4 py-4 text-gray-700">{{ $report->reportedByUser?->name ?? 'Not attributed' }}</td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.damage-reports.show', $report) }}">View</a>
                                    <a class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:border-gray-400 hover:text-gray-950" href="{{ route('web.damage-reports.edit', $report) }}">Edit</a>
                                    <a class="rounded-md border border-rose-300 px-3 py-2 text-xs font-medium text-rose-700 hover:border-rose-400 hover:text-rose-900" href="{{ route('web.damage-reports.show', $report) }}#repair-update-form">Repair update</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-10 text-center text-gray-600" colspan="8">No damage reports matched this queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-4 py-4">
            {{ $reports->links() }}
        </div>
    </section>
@endsection
