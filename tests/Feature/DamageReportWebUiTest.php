<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\DamageSeverity;
use App\Enums\DamageStatus;
use App\Enums\RepairUpdateType;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\DamageReport;
use App\Models\Location;
use App\Models\RepairUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DamageReportWebUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_dashboard_defaults_to_open_reports_and_shows_summary_counts(): void
    {
        [$asset, $location] = $this->createAssetWithContext();

        $reported = DamageReport::create([
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'title' => 'Cracked display',
            'description' => 'Display is cracked.',
            'severity' => DamageSeverity::High->value,
            'status' => DamageStatus::Reported->value,
        ]);

        $inProgress = DamageReport::create([
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'title' => 'Loose handle',
            'description' => 'Handle is wobbling.',
            'severity' => DamageSeverity::Medium->value,
            'status' => DamageStatus::InProgress->value,
        ]);

        $resolved = DamageReport::create([
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'title' => 'Battery issue fixed',
            'description' => 'Battery replacement done.',
            'severity' => DamageSeverity::Low->value,
            'status' => DamageStatus::Resolved->value,
            'resolved_at' => now(),
        ]);

        $this->get('/damage-reports')
            ->assertOk()
            ->assertSee('Damage and repair queue')
            ->assertSee($reported->title)
            ->assertSee($inProgress->title)
            ->assertDontSee($resolved->title)
            ->assertSee('1 reported')
            ->assertSee('1 in progress')
            ->assertSee('1 resolved');
    }

    public function test_dashboard_filters_narrow_results_and_preserve_query_strings(): void
    {
        [$asset, $location] = $this->createAssetWithContext('AST-DMG-Q-001', 'Queue Asset');
        [$otherAsset, $otherLocation] = $this->createAssetWithContext('AST-DMG-Q-002', 'Other Asset', 'WARD-Q-2', 'Ward Queue 2');
        $reporter = User::factory()->create();
        $otherReporter = User::factory()->create();

        foreach (range(1, 16) as $number) {
            DamageReport::create([
                'asset_id' => $asset->id,
                'reported_by_user_id' => $reporter->id,
                'location_id' => $location->id,
                'title' => 'Queue issue '.$number,
                'description' => 'Matching report '.$number,
                'severity' => DamageSeverity::High->value,
                'status' => DamageStatus::InProgress->value,
                'reported_at' => '2026-05-10 12:00:00',
            ]);
        }

        DamageReport::create([
            'asset_id' => $otherAsset->id,
            'reported_by_user_id' => $otherReporter->id,
            'location_id' => $otherLocation->id,
            'title' => 'Unrelated report',
            'description' => 'Should be filtered out.',
            'severity' => DamageSeverity::Low->value,
            'status' => DamageStatus::Reported->value,
            'reported_at' => '2026-05-11 12:00:00',
        ]);

        $response = $this->get('/damage-reports?asset_id='.$asset->id.'&status='.DamageStatus::InProgress->value.'&severity='.DamageSeverity::High->value.'&location_id='.$location->id.'&reported_by_user_id='.$reporter->id.'&date_from=2026-05-10T00:00&date_to=2026-05-10T23:59');

        $response
            ->assertOk()
            ->assertSee('Queue issue 1')
            ->assertDontSee('Unrelated report')
            ->assertSee('page=2', false)
            ->assertSee('asset_id='.$asset->id, false)
            ->assertSee('status='.DamageStatus::InProgress->value, false)
            ->assertSee('severity='.DamageSeverity::High->value, false)
            ->assertSee('location_id='.$location->id, false)
            ->assertSee('reported_by_user_id='.$reporter->id, false);
    }

    public function test_create_pages_render_and_asset_context_preselects_asset(): void
    {
        [$asset, $location] = $this->createAssetWithContext();
        $user = User::factory()->create();

        $this->get('/damage-reports/create')
            ->assertOk()
            ->assertSee('Report asset damage')
            ->assertSee($asset->name)
            ->assertSee($user->email);

        $this->get('/damage-reports/create?asset_id='.$asset->id)
            ->assertOk()
            ->assertSee('value="'.$asset->id.'" selected', false)
            ->assertSee($location->name);
    }

    public function test_damage_report_can_be_created_with_asset_location_defaults_in_web_ui(): void
    {
        [$asset, $location] = $this->createAssetWithContext();

        $this->post('/damage-reports', [
            'asset_id' => $asset->id,
            'title' => 'Broken wheel',
            'description' => 'The rear wheel is jammed.',
        ])
            ->assertRedirect('/damage-reports/1')
            ->assertSessionHas('status_message', 'Damage report created successfully.');

        $this->assertDatabaseHas('damage_reports', [
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'title' => 'Broken wheel',
            'severity' => DamageSeverity::Medium->value,
            'status' => DamageStatus::Reported->value,
        ]);
    }

    public function test_report_detail_renders_repair_timeline_and_repair_updates_advance_status(): void
    {
        [$asset, $location] = $this->createAssetWithContext();
        $manager = User::factory()->create();
        $report = DamageReport::create([
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'title' => 'Screen flicker',
            'description' => 'Display flickers during use.',
            'severity' => DamageSeverity::Medium->value,
            'status' => DamageStatus::Reported->value,
            'reported_at' => '2026-05-10 09:00:00',
        ]);

        RepairUpdate::create([
            'damage_report_id' => $report->id,
            'updated_by_user_id' => $manager->id,
            'update_type' => RepairUpdateType::Inspection->value,
            'status_after' => DamageStatus::InProgress->value,
            'result_summary' => 'Inspection started',
            'notes' => 'Diagnostics underway.',
            'logged_at' => '2026-05-10 10:00:00',
        ]);

        RepairUpdate::create([
            'damage_report_id' => $report->id,
            'updated_by_user_id' => $manager->id,
            'update_type' => RepairUpdateType::Repair->value,
            'status_after' => DamageStatus::InProgress->value,
            'result_summary' => 'Parts ordered',
            'notes' => 'Waiting on replacement cable.',
            'logged_at' => '2026-05-10 11:00:00',
        ]);

        $this->get('/damage-reports/'.$report->id)
            ->assertOk()
            ->assertSee($asset->name)
            ->assertSee($location->name)
            ->assertSeeInOrder(['Parts ordered', 'Inspection started']);

        $this->post('/damage-reports/'.$report->id.'/repair-updates', [
            'updated_by_user_id' => $manager->id,
            'update_type' => RepairUpdateType::Repair->value,
            'status_after' => DamageStatus::Resolved->value,
            'result_summary' => 'Repair completed',
            'notes' => 'Cable replaced and tested.',
            'logged_at' => '2026-05-10 12:00:00',
        ])
            ->assertRedirect('/damage-reports/'.$report->id)
            ->assertSessionHas('status_message', 'Repair update logged successfully.');

        $report->refresh();

        $this->assertSame(DamageStatus::Resolved, $report->status);
        $this->assertNotNull($report->resolved_at);
        $this->assertDatabaseHas('repair_updates', [
            'damage_report_id' => $report->id,
            'result_summary' => 'Repair completed',
            'status_after' => DamageStatus::Resolved->value,
        ]);
    }

    public function test_damage_report_can_be_updated_and_deleted_in_web_ui(): void
    {
        [$asset, $location] = $this->createAssetWithContext();
        $report = DamageReport::create([
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'title' => 'Damaged shell',
            'description' => 'Housing is cracked.',
            'severity' => DamageSeverity::Low->value,
            'status' => DamageStatus::Reported->value,
        ]);

        $repairUpdate = RepairUpdate::create([
            'damage_report_id' => $report->id,
            'update_type' => RepairUpdateType::Note->value,
            'result_summary' => 'Initial review',
            'logged_at' => '2026-05-10 08:30:00',
        ]);

        $this->patch('/damage-reports/'.$report->id, [
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'title' => 'Damaged shell updated',
            'description' => 'Housing is cracked and sharp.',
            'severity' => DamageSeverity::High->value,
            'status' => DamageStatus::Resolved->value,
        ])
            ->assertRedirect('/damage-reports/'.$report->id)
            ->assertSessionHas('status_message', 'Damage report updated successfully.');

        $report->refresh();
        $this->assertSame(DamageStatus::Resolved, $report->status);
        $this->assertNotNull($report->resolved_at);

        $this->patch('/damage-reports/'.$report->id, [
            'status' => DamageStatus::InProgress->value,
        ])
            ->assertRedirect('/damage-reports/'.$report->id);

        $this->assertNull($report->fresh()->resolved_at);

        $this->delete('/damage-reports/'.$report->id)
            ->assertRedirect('/damage-reports')
            ->assertSessionHas('status_message', 'Damage report deleted successfully.');

        $this->assertDatabaseMissing('damage_reports', ['id' => $report->id]);
        $this->assertDatabaseMissing('repair_updates', ['id' => $repairUpdate->id]);
    }

    private function createAssetWithContext(
        string $assetCode = 'AST-DMG-WEB-001',
        string $assetName = 'Portable Ultrasound',
        string $locationCode = 'WARD-DMG-WEB-1',
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
