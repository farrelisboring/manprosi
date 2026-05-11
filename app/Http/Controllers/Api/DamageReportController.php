<?php

namespace App\Http\Controllers\Api;

use App\Enums\DamageStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDamageReportRequest;
use App\Http\Requests\UpdateDamageReportRequest;
use App\Http\Resources\DamageReportResource;
use App\Models\DamageReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class DamageReportController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'asset_id' => ['nullable', 'integer', Rule::exists('assets', 'id')],
            'status' => ['nullable', Rule::enum(DamageStatus::class)],
            'severity' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')],
            'reported_by_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $reports = DamageReport::query()
            ->with($this->relations())
            ->when($validated['asset_id'] ?? null, fn ($query, $assetId) => $query->forAsset((int) $assetId))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['severity'] ?? null, fn ($query, $severity) => $query->withSeverity($severity))
            ->when($validated['location_id'] ?? null, fn ($query, $locationId) => $query->where('location_id', $locationId))
            ->when($validated['reported_by_user_id'] ?? null, fn ($query, $userId) => $query->where('reported_by_user_id', $userId))
            ->when($validated['date_from'] ?? null, fn ($query, $date) => $query->where('reported_at', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($query, $date) => $query->where('reported_at', '<=', $date))
            ->orderByDesc('reported_at')
            ->orderByDesc('id')
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        return DamageReportResource::collection($reports);
    }

    public function store(StoreDamageReportRequest $request): JsonResponse
    {
        $damageReport = DamageReport::create($request->validatedWithDefaults());

        return DamageReportResource::make(
            $damageReport->refresh()->load($this->relations())
        )
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(DamageReport $damageReport): DamageReportResource
    {
        return DamageReportResource::make(
            $damageReport->load($this->relations())
        );
    }

    public function update(UpdateDamageReportRequest $request, DamageReport $damageReport): DamageReportResource
    {
        $damageReport->update($request->validatedForUpdate($damageReport));

        return DamageReportResource::make(
            $damageReport->refresh()->load($this->relations())
        );
    }

    public function destroy(DamageReport $damageReport): Response
    {
        $damageReport->delete();

        return response()->noContent();
    }

    private function relations(): array
    {
        return [
            'asset.category',
            'reportedByUser',
            'location',
            'repairUpdates' => fn ($query) => $query->recentFirst(),
            'repairUpdates.updatedByUser',
        ];
    }
}
