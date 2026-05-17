<?php

namespace App\Http\Controllers\Web;

use App\Enums\DamageSeverity;
use App\Enums\DamageStatus;
use App\Enums\RepairUpdateType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDamageReportRequest;
use App\Http\Requests\UpdateDamageReportRequest;
use App\Models\Asset;
use App\Models\DamageReport;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DamageReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate($this->filterRules());
        $validated = $this->normalizeFilterDates($validated);
        $selectedStatusFilter = $validated['status'] ?? '';
        $showOpenOnly = ! filled($selectedStatusFilter);
        $reportFilters = $validated;

        if ($selectedStatusFilter === 'all') {
            unset($reportFilters['status']);
        }

        $reports = DamageReport::query()
            ->with($this->queueRelations())
            ->withFilters($reportFilters)
            ->when($showOpenOnly, fn ($query) => $query->open())
            ->recentFirst()
            ->paginate(15)
            ->withQueryString();

        return view('damage-reports.index', [
            'reports' => $reports,
            'summaryCounts' => $this->summaryCounts($validated),
            'assets' => $this->assets(),
            'locations' => $this->locations(),
            'severityOptions' => DamageSeverity::cases(),
            'statusOptions' => DamageStatus::cases(),
            'selectedStatusFilter' => $selectedStatusFilter,
        ]);
    }

    public function create(Request $request): View
    {
        return view('damage-reports.create', $this->formViewData($request));
    }

    public function store(StoreDamageReportRequest $request): RedirectResponse
    {
        $damageReport = DamageReport::create($request->validatedWithDefaults());

        return redirect()
            ->route('web.damage-reports.show', $damageReport)
            ->with('status_message', 'Damage report created successfully.')
            ->with('status_type', 'success');
    }

    public function show(DamageReport $damageReport): View
    {
        return view('damage-reports.show', [
            'damageReport' => $damageReport->load($this->detailRelations()),
            'statusOptions' => DamageStatus::cases(),
            'repairUpdateTypes' => $this->repairUpdateTypes(),
            'users' => $this->users(),
        ]);
    }

    public function edit(Request $request, DamageReport $damageReport): View
    {
        return view('damage-reports.edit', $this->formViewData($request, $damageReport));
    }

    public function update(UpdateDamageReportRequest $request, DamageReport $damageReport): RedirectResponse
    {
        $damageReport->update($request->validatedForUpdate($damageReport));

        return redirect()
            ->route('web.damage-reports.show', $damageReport)
            ->with('status_message', 'Damage report updated successfully.')
            ->with('status_type', 'success');
    }

    public function destroy(DamageReport $damageReport): RedirectResponse
    {
        $damageReport->delete();

        return redirect()
            ->route('web.damage-reports.index')
            ->with('status_message', 'Damage report deleted successfully.')
            ->with('status_type', 'success');
    }

    private function formViewData(Request $request, ?DamageReport $damageReport = null): array
    {
        $selectedAsset = $this->selectedAsset($request, $damageReport);
        $selectedLocationId = old('location_id', $damageReport?->location_id ?? $selectedAsset?->current_location_id);

        return [
            'damageReport' => $damageReport?->loadMissing($this->detailRelations()),
            'selectedAsset' => $selectedAsset,
            'assets' => $this->assets(),
            'locations' => $this->locations(),
            'users' => $this->users(),
            'severityOptions' => DamageSeverity::cases(),
            'statusOptions' => DamageStatus::cases(),
            'selectedLocationId' => $selectedLocationId === '' ? null : $selectedLocationId,
            'blockedByMissingAssets' => $this->assets()->isEmpty(),
        ];
    }

    private function selectedAsset(Request $request, ?DamageReport $damageReport = null): ?Asset
    {
        $selectedAssetId = old('asset_id', $damageReport?->asset_id ?? $request->query('asset_id'));

        if ($selectedAssetId === null || $selectedAssetId === '') {
            return null;
        }

        return Asset::query()
            ->with(['category', 'currentLocation'])
            ->find($selectedAssetId);
    }

    private function filterRules(): array
    {
        return [
            'asset_id' => ['nullable', 'integer', Rule::exists('assets', 'id')],
            'status' => ['nullable', Rule::in([
                'all',
                ...collect(DamageStatus::cases())->map(fn (DamageStatus $status) => $status->value)->all(),
            ])],
            'severity' => ['nullable', Rule::enum(DamageSeverity::class)],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ];
    }

    private function normalizeFilterDates(array $validated): array
    {
        $dateFrom = filled($validated['date_from'] ?? null)
            ? Carbon::parse($validated['date_from'])->startOfDay()
            : null;
        $dateTo = filled($validated['date_to'] ?? null)
            ? Carbon::parse($validated['date_to'])->endOfDay()
            : null;

        if ($dateFrom && $dateTo && $dateTo->lt($dateFrom)) {
            throw ValidationException::withMessages([
                'date_to' => 'The end date must be on or after the start date.',
            ]);
        }

        if ($dateFrom) {
            $validated['date_from'] = $dateFrom->toDateTimeString();
        }

        if ($dateTo) {
            $validated['date_to'] = $dateTo->toDateTimeString();
        }

        return $validated;
    }

    private function summaryCounts(array $validated): array
    {
        $filtersWithoutStatus = collect($validated)
            ->except('status')
            ->all();

        $counts = DamageReport::query()
            ->withFilters($filtersWithoutStatus)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count) => (int) $count)
            ->all();

        return [
            DamageStatus::Reported->value => $counts[DamageStatus::Reported->value] ?? 0,
            DamageStatus::InProgress->value => $counts[DamageStatus::InProgress->value] ?? 0,
            DamageStatus::Resolved->value => $counts[DamageStatus::Resolved->value] ?? 0,
        ];
    }

    private function assets(): Collection
    {
        return Asset::query()
            ->orderBy('name')
            ->get(['id', 'asset_code', 'name', 'current_location_id']);
    }

    private function locations(): Collection
    {
        return Location::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'type', 'floor_number']);
    }

    private function users(): Collection
    {
        // TODO: Replace manual user selection with authenticated current-user attribution once auth exists.
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function repairUpdateTypes(): array
    {
        return collect(RepairUpdateType::cases())
            ->map(fn (RepairUpdateType $type) => [
                'value' => $type->value,
                'label' => Str::headline($type->value),
            ])
            ->all();
    }

    private function detailRelations(): array
    {
        return [
            'asset.category',
            'asset.currentLocation',
            'reportedByUser',
            'location',
            'repairUpdates' => fn ($query) => $query->recentFirst(),
            'repairUpdates.updatedByUser',
        ];
    }

    private function queueRelations(): array
    {
        return [
            'asset.category',
            'reportedByUser',
            'location',
        ];
    }
}
