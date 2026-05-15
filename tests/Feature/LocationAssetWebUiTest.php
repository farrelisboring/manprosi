<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\LocationMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationAssetWebUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_location_viewer_loads_with_dropdown_and_chooser_first_state(): void
    {
        $location = $this->createLocation('LOC-VIEW-001', 'Ward A');

        $this->get('/locations/assets')
            ->assertOk()
            ->assertSee('Assets by location')
            ->assertSee('Choose a location to begin')
            ->assertSee($location->name)
            ->assertSee('data-poll-disabled="true"', false);
    }

    public function test_selected_location_shows_only_direct_assignments_and_status_counts(): void
    {
        $category = $this->createCategory();
        $selectedLocation = $this->createLocation('LOC-VIEW-010', 'Ward North', 'room', 3);
        $childLocation = $this->createLocation('LOC-VIEW-011', 'Ward North Storage', 'storage', 3, $selectedLocation->id);
        $otherLocation = $this->createLocation('LOC-VIEW-012', 'Ward South', 'room', 3);
        $selectedMap = $this->createMap($selectedLocation, 'Ward North Map');

        Asset::create([
            'asset_code' => 'LOC-ASSET-001',
            'name' => 'Portable Monitor',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $selectedLocation->id,
            'current_map_id' => $selectedMap->id,
            'position_x' => 10.5,
            'position_y' => 20.5,
        ]);

        Asset::create([
            'asset_code' => 'LOC-ASSET-002',
            'name' => 'Infusion Pump',
            'category_id' => $category->id,
            'status' => AssetStatus::InUse->value,
            'current_location_id' => $selectedLocation->id,
        ]);

        Asset::create([
            'asset_code' => 'LOC-ASSET-003',
            'name' => 'Child Room Bed',
            'category_id' => $category->id,
            'status' => AssetStatus::Maintenance->value,
            'current_location_id' => $childLocation->id,
        ]);

        Asset::create([
            'asset_code' => 'LOC-ASSET-004',
            'name' => 'Sibling Ventilator',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $otherLocation->id,
        ]);

        $this->get('/locations/assets?location_id='.$selectedLocation->id)
            ->assertOk()
            ->assertSee('Ward North')
            ->assertSee('LOC-VIEW-010')
            ->assertSee('Room')
            ->assertSee('Floor 3')
            ->assertSee('Portable Monitor')
            ->assertSee('Infusion Pump')
            ->assertDontSee('Child Room Bed')
            ->assertDontSee('Sibling Ventilator')
            ->assertSee('Ward North Map')
            ->assertSee('>2<', false)
            ->assertSee('>1<', false);
    }

    public function test_selected_location_with_no_assets_shows_empty_state(): void
    {
        $location = $this->createLocation('LOC-VIEW-020', 'Ward Empty');

        $this->get('/locations/assets?location_id='.$location->id)
            ->assertOk()
            ->assertSee('Ward Empty')
            ->assertSee('No assets are currently assigned to this location.');
    }

    public function test_viewer_shows_blocked_state_when_no_active_locations_exist(): void
    {
        Location::create([
            'code' => 'LOC-VIEW-INACTIVE',
            'name' => 'Inactive Store',
            'type' => 'storage',
            'is_active' => false,
        ]);

        $this->get('/locations/assets')
            ->assertOk()
            ->assertSee('Location viewer is blocked')
            ->assertDontSee('Choose a location to begin');
    }

    public function test_refresh_endpoint_returns_updated_partial_after_assignment_changes(): void
    {
        $category = $this->createCategory();
        $selectedLocation = $this->createLocation('LOC-VIEW-030', 'ICU East');
        $otherLocation = $this->createLocation('LOC-VIEW-031', 'ICU West');

        Asset::create([
            'asset_code' => 'LOC-ASSET-030',
            'name' => 'Bedside Monitor',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $selectedLocation->id,
        ]);

        $incomingAsset = Asset::create([
            'asset_code' => 'LOC-ASSET-031',
            'name' => 'Portable Defibrillator',
            'category_id' => $category->id,
            'status' => AssetStatus::Maintenance->value,
            'current_location_id' => $otherLocation->id,
        ]);

        $this->get('/locations/assets/panel?location_id='.$selectedLocation->id)
            ->assertOk()
            ->assertSee('Bedside Monitor')
            ->assertDontSee('Portable Defibrillator');

        $incomingAsset->update([
            'current_location_id' => $selectedLocation->id,
        ]);

        $this->get('/locations/assets/panel?location_id='.$selectedLocation->id)
            ->assertOk()
            ->assertSee('Bedside Monitor')
            ->assertSee('Portable Defibrillator')
            ->assertSee('>2<', false)
            ->assertSee('>1<', false);
    }

    private function createCategory(string $code = 'LOC-VIEW-CAT', string $name = 'Diagnostics'): AssetCategory
    {
        return AssetCategory::create([
            'code' => $code,
            'name' => $name,
        ]);
    }

    private function createLocation(
        string $code,
        string $name,
        string $type = 'room',
        ?int $floorNumber = 1,
        ?int $parentId = null,
    ): Location {
        return Location::create([
            'parent_id' => $parentId,
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'floor_number' => $floorNumber,
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
