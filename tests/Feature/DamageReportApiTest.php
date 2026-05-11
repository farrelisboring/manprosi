<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\DamageSeverity;
use App\Enums\DamageStatus;
use App\Enums\RepairUpdateType;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\DamageReport;
use App\Models\Location;
use App\Models\RepairUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DamageReportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_damage_report_can_be_created_with_schema_defaults_and_asset_location(): void
    {
        [$asset, $location] = $this->createAssetWithContext();
        $reporter = User::factory()->create(['role' => UserRole::Staff->value]);

        $response = $this->postJson('/api/damage-reports', [
            'asset_id' => $asset->id,
            'reported_by_user_id' => $reporter->id,
            'title' => 'Cracked display',
            'description' => 'The screen is cracked and hard to read.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Cracked display')
            ->assertJsonPath('data.severity', DamageSeverity::Medium->value)
            ->assertJsonPath('data.status', DamageStatus::Reported->value)
            ->assertJsonPath('data.asset.id', $asset->id)
            ->assertJsonPath('data.asset.category.name', 'Imaging Equipment AST-DMG-001')
            ->assertJsonPath('data.location.id', $location->id)
            ->assertJsonPath('data.reported_by_user.id', $reporter->id)
            ->assertJsonPath('data.repair_updates', []);

        $this->assertDatabaseHas('damage_reports', [
            'asset_id' => $asset->id,
            'reported_by_user_id' => $reporter->id,
            'location_id' => $location->id,
            'title' => 'Cracked display',
            'severity' => DamageSeverity::Medium->value,
            'status' => DamageStatus::Reported->value,
        ]);
    }

    public function test_damage_reports_can_be_listed_filtered_and_viewed_with_repair_context(): void
    {
        [$asset, $location] = $this->createAssetWithContext();
        [$otherAsset, $otherLocation] = $this->createAssetWithContext('AST-DMG-OTHER', 'Other Asset', 'WARD-OTHER', 'Other Ward');
        $reporter = User::factory()->create(['role' => UserRole::Nurse->value]);
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);

        $olderReport = DamageReport::create([
            'asset_id' => $asset->id,
            'reported_by_user_id' => $reporter->id,
            'location_id' => $location->id,
            'title' => 'Loose wheel',
            'description' => 'One wheel is loose.',
            'severity' => DamageSeverity::Low->value,
            'status' => DamageStatus::Reported->value,
            'reported_at' => '2026-05-09 08:00:00',
        ]);

        $matchingReport = DamageReport::create([
            'asset_id' => $asset->id,
            'reported_by_user_id' => $reporter->id,
            'location_id' => $location->id,
            'title' => 'Battery warning',
            'description' => 'Battery warning appears during startup.',
            'severity' => DamageSeverity::High->value,
            'status' => DamageStatus::InProgress->value,
            'reported_at' => '2026-05-10 10:00:00',
        ]);

        $otherReport = DamageReport::create([
            'asset_id' => $otherAsset->id,
            'location_id' => $otherLocation->id,
            'title' => 'Other report',
            'description' => 'Unrelated report.',
            'severity' => DamageSeverity::High->value,
            'status' => DamageStatus::InProgress->value,
            'reported_at' => '2026-05-10 11:00:00',
        ]);

        RepairUpdate::create([
            'damage_report_id' => $matchingReport->id,
            'updated_by_user_id' => $manager->id,
            'update_type' => RepairUpdateType::Inspection->value,
            'status_after' => DamageStatus::InProgress->value,
            'result_summary' => 'Inspection started',
            'notes' => 'Technician assigned.',
            'logged_at' => '2026-05-10 11:30:00',
        ]);

        $this->getJson('/api/damage-reports')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', $otherReport->id)
            ->assertJsonPath('data.1.id', $matchingReport->id)
            ->assertJsonPath('data.2.id', $olderReport->id)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'severity',
                        'status',
                        'asset',
                        'location',
                        'reported_by_user',
                        'repair_updates',
                    ],
                ],
                'links',
                'meta',
            ]);

        $query = http_build_query([
            'asset_id' => $asset->id,
            'status' => DamageStatus::InProgress->value,
            'severity' => DamageSeverity::High->value,
            'location_id' => $location->id,
            'reported_by_user_id' => $reporter->id,
            'date_from' => '2026-05-10 00:00:00',
            'date_to' => '2026-05-10 23:59:59',
        ]);

        $this->getJson('/api/damage-reports?'.$query)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingReport->id);

        $this->getJson('/api/damage-reports/'.$matchingReport->id)
            ->assertOk()
            ->assertJsonPath('data.id', $matchingReport->id)
            ->assertJsonPath('data.asset.id', $asset->id)
            ->assertJsonPath('data.location.id', $location->id)
            ->assertJsonPath('data.repair_updates.0.result_summary', 'Inspection started')
            ->assertJsonPath('data.repair_updates.0.updated_by_user.id', $manager->id);
    }

    public function test_damage_report_can_be_updated_and_resolution_timestamp_is_managed(): void
    {
        [$asset, $location] = $this->createAssetWithContext();
        $newLocation = $this->createLocation('WARD-DMG-2', 'Ward Damage 2');
        $report = DamageReport::create([
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'title' => 'Display issue',
            'description' => 'Original description.',
            'severity' => DamageSeverity::Medium->value,
            'status' => DamageStatus::Reported->value,
            'reported_at' => '2026-05-10 09:00:00',
        ]);

        $resolveResponse = $this->patchJson('/api/damage-reports/'.$report->id, [
            'title' => 'Display replaced',
            'description' => 'Display replacement completed.',
            'severity' => DamageSeverity::High->value,
            'location_id' => $newLocation->id,
            'status' => DamageStatus::Resolved->value,
        ]);

        $resolveResponse
            ->assertOk()
            ->assertJsonPath('data.title', 'Display replaced')
            ->assertJsonPath('data.severity', DamageSeverity::High->value)
            ->assertJsonPath('data.status', DamageStatus::Resolved->value)
            ->assertJsonPath('data.location.id', $newLocation->id);

        $report->refresh();
        $this->assertNotNull($report->resolved_at);

        $this->patchJson('/api/damage-reports/'.$report->id, [
            'status' => DamageStatus::InProgress->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', DamageStatus::InProgress->value)
            ->assertJsonPath('data.resolved_at', null);

        $this->assertNull($report->refresh()->resolved_at);
    }

    public function test_damage_report_delete_hard_deletes_report_and_cascades_repair_updates(): void
    {
        [$asset, $location] = $this->createAssetWithContext();
        $report = DamageReport::create([
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'title' => 'Broken casing',
            'description' => 'Outer casing is cracked.',
            'severity' => DamageSeverity::Medium->value,
            'status' => DamageStatus::InProgress->value,
            'reported_at' => '2026-05-10 09:00:00',
        ]);

        $repairUpdate = RepairUpdate::create([
            'damage_report_id' => $report->id,
            'update_type' => RepairUpdateType::Repair->value,
            'status_after' => DamageStatus::InProgress->value,
            'result_summary' => 'Repair scheduled',
            'logged_at' => '2026-05-10 10:00:00',
        ]);

        $this->deleteJson('/api/damage-reports/'.$report->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('damage_reports', [
            'id' => $report->id,
        ]);
        $this->assertDatabaseMissing('repair_updates', [
            'id' => $repairUpdate->id,
        ]);
    }

    public function test_damage_report_validation_rejects_invalid_payloads(): void
    {
        [$asset] = $this->createAssetWithContext();

        $this->postJson('/api/damage-reports', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['asset_id', 'title', 'description']);

        $this->postJson('/api/damage-reports', [
            'asset_id' => 999,
            'reported_by_user_id' => 999,
            'location_id' => 999,
            'title' => 'Invalid report',
            'description' => 'Invalid linked records.',
            'severity' => 'critical',
            'status' => 'ignored',
            'reported_at' => 'not-a-date',
            'resolved_at' => 'still-not-a-date',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'asset_id',
                'reported_by_user_id',
                'location_id',
                'severity',
                'status',
                'reported_at',
                'resolved_at',
            ]);

        $this->getJson('/api/damage-reports?date_from=2026-05-11&date_to=2026-05-10')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date_to']);

        $report = DamageReport::create([
            'asset_id' => $asset->id,
            'title' => 'Valid report',
            'description' => 'Valid report.',
            'severity' => DamageSeverity::Low->value,
            'status' => DamageStatus::Reported->value,
        ]);

        $this->patchJson('/api/damage-reports/'.$report->id, [
            'severity' => null,
            'status' => null,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['severity', 'status']);
    }

    private function createAssetWithContext(
        string $assetCode = 'AST-DMG-001',
        string $assetName = 'Portable Ultrasound',
        string $locationCode = 'WARD-DMG-1',
        string $locationName = 'Ward Damage 1',
    ): array {
        $category = AssetCategory::create([
            'code' => 'IMG-'.$assetCode,
            'name' => 'Imaging Equipment '.$assetCode,
        ]);
        $location = $this->createLocation($locationCode, $locationName);
        $asset = Asset::create([
            'asset_code' => $assetCode,
            'name' => $assetName,
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $location->id,
        ]);

        return [$asset, $location, $category];
    }

    private function createLocation(string $code, string $name): Location
    {
        return Location::create([
            'code' => $code,
            'name' => $name,
            'type' => 'room',
            'floor_number' => 1,
            'is_active' => true,
        ]);
    }
}
