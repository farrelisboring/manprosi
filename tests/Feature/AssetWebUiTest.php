<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\GeofenceRuleType;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetGeofence;
use App\Models\Location;
use App\Models\LocationMap;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetWebUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->signInAsRole(UserRole::Staff);
    }

    public function test_dashboard_loads_and_shows_asset_summary_counts(): void
    {
        $category = $this->createCategory();
        Asset::create([
            'asset_code' => 'AST-DASH-001',
            'name' => 'Ultrasound',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
        ]);
        Asset::create([
            'asset_code' => 'AST-DASH-002',
            'name' => 'Infusion Pump',
            'category_id' => $category->id,
            'status' => AssetStatus::InUse->value,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Pantau aset, cari perangkat, dan tindak lanjuti laporan dari satu panel.')
            ->assertSee('Total Aset')
            ->assertSee('Ultrasound')
            ->assertSee('Infusion Pump');
    }

    public function test_asset_index_uses_browser_filters_without_name_search_and_preserves_query_string_in_pagination(): void
    {
        $category = $this->createCategory('CAT-WEB-001', 'Imaging');
        $otherCategory = $this->createCategory('CAT-WEB-002', 'Beds');
        $location = $this->createLocation('LOC-WEB-001', 'Ward A');
        $otherLocation = $this->createLocation('LOC-WEB-002', 'Ward B');

        foreach (range(1, 14) as $number) {
            Asset::create([
                'asset_code' => 'AST-WEB-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'name' => 'Portable Ultrasound '.$number,
                'category_id' => $category->id,
                'status' => AssetStatus::Available->value,
                'current_location_id' => $location->id,
                'created_at' => Carbon::parse('2026-01-01 08:00:00')->addMinutes($number),
                'updated_at' => Carbon::parse('2026-01-01 08:00:00')->addMinutes($number),
            ]);
        }

        Asset::create([
            'asset_code' => 'AST-WEB-999',
            'name' => 'Patient Bed',
            'category_id' => $otherCategory->id,
            'status' => AssetStatus::Maintenance->value,
            'current_location_id' => $otherLocation->id,
        ]);

        Asset::create([
            'asset_code' => 'AST-WEB-777',
            'name' => 'Infusion Pump Browser Candidate',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $location->id,
            'created_at' => Carbon::parse('2026-01-01 09:00:00'),
            'updated_at' => Carbon::parse('2026-01-01 09:00:00'),
        ]);

        Asset::create([
            'asset_code' => 'AST-WEB-778',
            'name' => 'Wheelchair Browser Candidate',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $location->id,
            'created_at' => Carbon::parse('2026-01-01 09:01:00'),
            'updated_at' => Carbon::parse('2026-01-01 09:01:00'),
        ]);

        $response = $this->get('/assets?search=Ultrasound&category_id='.$category->id.'&current_location_id='.$location->id.'&status=available');

        $response
            ->assertOk()
            ->assertSee('Kategori')
            ->assertDontSee('Telusuri')
            ->assertSee('Portable Ultrasound 1')
            ->assertSee('Infusion Pump Browser Candidate')
            ->assertDontSee('Patient Bed')
            ->assertSee('page=2', false)
            ->assertSee('category_id='.$category->id, false)
            ->assertSee('current_location_id='.$location->id, false)
            ->assertSee('status=available', false)
            ->assertDontSee('search=Ultrasound', false);
    }

    public function test_asset_create_and_edit_pages_render_supporting_data_and_constrained_maps(): void
    {
        $category = $this->createCategory();
        $location = $this->createLocation('LOC-WEB-010', 'Ward Alpha');
        $otherLocation = $this->createLocation('LOC-WEB-011', 'Ward Beta');
        $map = $this->createMap($location, 'Ward Alpha Map');
        $otherMap = $this->createMap($otherLocation, 'Ward Beta Map');
        $asset = Asset::create([
            'asset_code' => 'AST-WEB-010',
            'name' => 'Monitor',
            'category_id' => $category->id,
            'current_location_id' => $location->id,
            'current_map_id' => $map->id,
        ]);

        $this->get('/assets/create')
            ->assertOk()
            ->assertSee('Menambahkan aset')
            ->assertSee($category->name)
            ->assertSee('value="available" selected', false)
            ->assertDontSee('Barcode value')
            ->assertDontSee('RFID tag')
            ->assertDontSee('Position X')
            ->assertDontSee('Position Y')
            ->assertSee('Peraturan Geofence')
            ->assertSee('Ketika Pindah Ruangan');

        $this->get('/assets/'.$asset->id.'/edit')
            ->assertOk()
            ->assertSee('Edit aset')
            ->assertSee('Monitor')
            ->assertDontSee('Current map');
    }

    public function test_asset_store_and_update_redirect_with_flash_messages(): void
    {
        $category = $this->createCategory();
        $location = $this->createLocation('LOC-WEB-020', 'Ward C');
        $map = $this->createMap($location, 'Ward C Map');
        $forbiddenLocation = $this->createLocation('LOC-WEB-021', 'Ward D');

        $storeResponse = $this->post('/assets', [
            'asset_code' => 'AST-WEB-020',
            'name' => 'Ventilator',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $location->id,
            'current_map_id' => $map->id,
            'position_x' => 11.5,
            'position_y' => 22.5,
            'geofence_enabled' => '1',
            'geofence_on_room_change' => '1',
            'geofence_forbidden_location_ids' => [$forbiddenLocation->id],
        ]);

        $asset = Asset::firstOrFail();

        $storeResponse
            ->assertRedirect('/assets/'.$asset->id)
            ->assertSessionHas('status_message', 'Asset created successfully.');

        $this->patch('/assets/'.$asset->id, [
            'name' => 'Ventilator Updated',
            'asset_code' => 'AST-WEB-020',
            'category_id' => $category->id,
            'current_location_id' => $location->id,
            'current_map_id' => $map->id,
            'position_x' => 30.1,
            'position_y' => 44.2,
            'geofence_enabled' => '1',
            'geofence_on_room_change' => '1',
            'geofence_forbidden_location_ids' => [$forbiddenLocation->id],
        ])
            ->assertRedirect('/assets/'.$asset->id)
            ->assertSessionHas('status_message', 'Asset updated successfully.');

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'name' => 'Ventilator Updated',
            'current_map_id' => $map->id,
        ]);
        $this->assertDatabaseHas('asset_geofences', [
            'asset_id' => $asset->id,
            'rule_type' => GeofenceRuleType::RoomChangeNotification->value,
            'location_id' => null,
        ]);
        $this->assertDatabaseHas('asset_geofences', [
            'asset_id' => $asset->id,
            'rule_type' => GeofenceRuleType::RestrictedEntry->value,
            'location_id' => $forbiddenLocation->id,
        ]);
    }

    public function test_asset_geofence_rules_are_removed_when_disabled_on_update(): void
    {
        $category = $this->createCategory('CAT-WEB-GEOFENCE', 'Geofence');
        $forbiddenLocation = $this->createLocation('LOC-WEB-GEOFENCE', 'Forbidden Ward');
        $asset = Asset::create([
            'asset_code' => 'AST-WEB-GEOFENCE',
            'name' => 'Geofence Asset',
            'category_id' => $category->id,
        ]);

        AssetGeofence::create([
            'name' => 'Ketika Pindah Ruangan',
            'asset_id' => $asset->id,
            'rule_type' => GeofenceRuleType::RoomChangeNotification->value,
            'is_active' => true,
        ]);

        AssetGeofence::create([
            'name' => 'Ruangan Terlarang',
            'asset_id' => $asset->id,
            'location_id' => $forbiddenLocation->id,
            'rule_type' => GeofenceRuleType::RestrictedEntry->value,
            'is_active' => true,
        ]);

        $this->patch('/assets/'.$asset->id, [
            'asset_code' => 'AST-WEB-GEOFENCE',
            'name' => 'Geofence Asset Updated',
            'category_id' => $category->id,
            'geofence_enabled' => '0',
        ])->assertRedirect('/assets/'.$asset->id);

        $this->assertDatabaseMissing('asset_geofences', [
            'asset_id' => $asset->id,
        ]);
    }

    public function test_asset_store_defaults_status_to_available_when_not_submitted(): void
    {
        $category = $this->createCategory('CAT-WEB-DEFAULT', 'Default Status');

        $this->post('/assets', [
            'asset_code' => 'AST-WEB-DEFAULT',
            'name' => 'Status Default Asset',
            'category_id' => $category->id,
        ])->assertRedirect('/assets/1');

        $this->assertDatabaseHas('assets', [
            'asset_code' => 'AST-WEB-DEFAULT',
            'status' => AssetStatus::Available->value,
        ]);
    }

    public function test_asset_validation_rejects_invalid_map_location_combinations(): void
    {
        $category = $this->createCategory();
        $location = $this->createLocation('LOC-WEB-030', 'Ward D');
        $otherLocation = $this->createLocation('LOC-WEB-031', 'Ward E');
        $wrongMap = $this->createMap($otherLocation, 'Ward E Map');

        $this->from('/assets/create')
            ->post('/assets', [
                'asset_code' => 'AST-WEB-030',
                'name' => 'Defibrillator',
                'category_id' => $category->id,
                'current_location_id' => $location->id,
                'current_map_id' => $wrongMap->id,
                'position_x' => 1,
                'position_y' => 2,
            ])
            ->assertRedirect('/assets/create')
            ->assertSessionHasErrors('current_map_id');
    }

    public function test_asset_show_and_delete_flows_work_in_the_blade_ui(): void
    {
        $category = $this->createCategory();
        $location = $this->createLocation('LOC-WEB-040', 'Ward F');
        $map = $this->createMap($location, 'Ward F Map');
        $asset = Asset::create([
            'asset_code' => 'AST-WEB-040',
            'name' => 'Patient Monitor',
            'category_id' => $category->id,
            'status' => AssetStatus::Maintenance->value,
            'current_location_id' => $location->id,
            'current_map_id' => $map->id,
            'position_x' => 10.1111,
            'position_y' => 20.2222,
            'qr_code_value' => 'QRWEB12345',
        ]);

        $this->get('/assets/'.$asset->id)
            ->assertOk()
            ->assertSee('Patient Monitor')
            ->assertSee('Assigned')
            ->assertSee('QRWEB12345')
            ->assertSee('data-qr-preview', false)
            ->assertSee('data-qr-download-link', false)
            ->assertSee('Download QR image')
            ->assertSee(route('web.qr-labels.redirect', $asset->qr_code_value), false)
            ->assertDontSee('Barcode value')
            ->assertDontSee('Printable code')
            ->assertDontSee('Map placement ready');

        $this->delete('/assets/'.$asset->id)
            ->assertRedirect('/assets')
            ->assertSessionHas('status_message', 'Asset deleted successfully.');

        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_qr_label_web_actions_generate_regenerate_and_delete_labels(): void
    {
        $category = $this->createCategory();
        $asset = Asset::create([
            'asset_code' => 'AST-WEB-050',
            'name' => 'ECG Machine',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
        ]);

        $this->post('/assets/'.$asset->id.'/qr-label')
            ->assertRedirect('/assets/'.$asset->id)
            ->assertSessionHas('status_message', 'QR label generated successfully.');

        $asset->refresh();
        $this->assertNotNull($asset->qr_code_value);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{10}$/', $asset->qr_code_value);
        $originalValue = $asset->qr_code_value;

        $this->get('/assets/'.$asset->id)
            ->assertOk()
            ->assertSee('data-qr-preview', false)
            ->assertSee('Download QR image')
            ->assertSee(route('web.qr-labels.redirect', $asset->qr_code_value), false)
            ->assertSee('Rendering QR preview');

        $this->patch('/assets/'.$asset->id.'/qr-label', [
            'confirm_regeneration' => '1',
        ])
            ->assertRedirect('/assets/'.$asset->id)
            ->assertSessionHas('status_message', 'QR label regenerated successfully.');

        $asset->refresh();
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{10}$/', $asset->qr_code_value);
        $this->assertNotSame($originalValue, $asset->qr_code_value);

        $this->get('/assets/'.$asset->id)
            ->assertOk()
            ->assertSee(route('web.qr-labels.redirect', $asset->qr_code_value), false);

        $this->delete('/assets/'.$asset->id.'/qr-label', [
            'confirm_deletion' => '1',
        ])
            ->assertRedirect('/assets/'.$asset->id)
            ->assertSessionHas('status_message', 'QR label deleted successfully.');

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'qr_code_value' => null,
        ]);

        $this->get('/assets/'.$asset->id)
            ->assertOk()
            ->assertSee('Download QR image')
            ->assertSee('Generate a QR label to preview the short-link code.')
            ->assertSee('A short-link URL will appear after a QR label is generated.');
    }

    public function test_create_page_shows_blocked_state_when_categories_are_missing(): void
    {
        $this->get('/assets/create')
            ->assertOk()
            ->assertSee('Asset creation is blocked');
    }

    public function test_short_qr_route_redirects_to_the_asset_detail_page(): void
    {
        $category = $this->createCategory();
        $asset = Asset::create([
            'asset_code' => 'AST-WEB-060',
            'name' => 'CT Scanner',
            'category_id' => $category->id,
            'qr_code_value' => 'JT8JHFK97H',
        ]);

        $this->get('/jt8jhfk97h')
            ->assertRedirect('/assets/'.$asset->id);

        $this->get('/ZZ99YY88XX')
            ->assertNotFound();
    }

    private function createCategory(string $code = 'CAT-WEB', string $name = 'Imaging'): AssetCategory
    {
        return AssetCategory::create([
            'code' => $code,
            'name' => $name,
        ]);
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

    private function createMap(Location $location, string $name): LocationMap
    {
        return LocationMap::create([
            'location_id' => $location->id,
            'name' => $name,
            'image_path' => 'maps/'.$location->code.'.png',
            'image_width' => 1200,
            'image_height' => 800,
        ]);
    }
}
