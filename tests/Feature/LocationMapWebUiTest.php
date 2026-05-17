<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\LocationMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationMapWebUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_location_map_pages_load(): void
    {
        $location = $this->createLocation('MAP-UI-001', 'Emergency Building');
        $locationMap = $this->createMap($location, 'Emergency Tower');

        $this->get('/location-maps')
            ->assertOk()
            ->assertSee('Location maps')
            ->assertSee($locationMap->name);

        $this->get('/location-maps/create')
            ->assertOk()
            ->assertSee('New location map');

        $this->get('/location-maps/'.$locationMap->id)
            ->assertOk()
            ->assertSee($locationMap->name)
            ->assertSee($location->name);

        $this->get('/location-maps/'.$locationMap->id.'/edit')
            ->assertOk()
            ->assertSee('Edit location map')
            ->assertSee('value="'.$locationMap->name.'"', false);
    }

    public function test_location_map_can_be_created_and_updated_without_image_fields(): void
    {
        $location = $this->createLocation('MAP-UI-010', 'Surgery');
        $otherLocation = $this->createLocation('MAP-UI-011', 'Recovery');

        $this->post('/location-maps', [
            'location_id' => $location->id,
            'name' => 'Surgery Tower',
            'notes' => 'North side.',
        ])->assertRedirect();

        $locationMap = LocationMap::query()->where('name', 'Surgery Tower')->firstOrFail();

        $this->assertSame($location->id, $locationMap->location_id);
        $this->assertNull($locationMap->image_path);
        $this->assertNull($locationMap->image_width);
        $this->assertNull($locationMap->image_height);

        $this->put('/location-maps/'.$locationMap->id, [
            'location_id' => $otherLocation->id,
            'name' => 'Recovery Tower',
            'notes' => 'Updated notes.',
        ])->assertRedirect('/location-maps/'.$locationMap->id);

        $locationMap->refresh();

        $this->assertSame($otherLocation->id, $locationMap->location_id);
        $this->assertSame('Recovery Tower', $locationMap->name);
        $this->assertSame('Updated notes.', $locationMap->notes);
    }

    public function test_location_map_delete_succeeds_when_unused(): void
    {
        $location = $this->createLocation('MAP-UI-020', 'Storage');
        $locationMap = $this->createMap($location, 'Storage Tower');

        $this->delete('/location-maps/'.$locationMap->id)
            ->assertRedirect('/location-maps?location_id='.$location->id);

        $this->assertDatabaseMissing('location_maps', [
            'id' => $locationMap->id,
        ]);
    }

    public function test_location_map_delete_is_blocked_when_assets_reference_the_map(): void
    {
        $category = AssetCategory::create([
            'code' => 'MAP-UI-CAT',
            'name' => 'Respiratory',
        ]);

        $location = $this->createLocation('MAP-UI-030', 'NICU');
        $locationMap = $this->createMap($location, 'NICU Tower');

        Asset::create([
            'asset_code' => 'MAP-UI-ASSET-001',
            'name' => 'Ventilator',
            'category_id' => $category->id,
            'current_location_id' => $location->id,
            'current_map_id' => $locationMap->id,
        ]);

        $this->get('/location-maps')
            ->assertOk()
            ->assertSee('data-blocked-action-message="This item cannot be deleted because related records still exist."', false);

        $this->delete('/location-maps/'.$locationMap->id)
            ->assertRedirect('/location-maps/'.$locationMap->id)
            ->assertSessionHas('status_message', 'This item cannot be deleted because related records still exist.');

        $this->assertDatabaseHas('location_maps', [
            'id' => $locationMap->id,
        ]);
    }

    public function test_location_maps_can_be_filtered_by_location(): void
    {
        $selectedLocation = $this->createLocation('MAP-UI-040', 'Oncology');
        $otherLocation = $this->createLocation('MAP-UI-041', 'Pediatrics');

        $selectedMap = $this->createMap($selectedLocation, 'Oncology Tower');
        $otherMap = $this->createMap($otherLocation, 'Pediatrics Tower');

        $this->get('/location-maps?location_id='.$selectedLocation->id)
            ->assertOk()
            ->assertSee($selectedMap->name)
            ->assertDontSee($otherMap->name);
    }

    private function createLocation(
        string $code,
        string $name,
        string $type = 'Building',
        ?int $floorNumber = 1,
    ): Location {
        return Location::create([
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
            'image_path' => null,
            'image_width' => null,
            'image_height' => null,
            'notes' => 'Test notes.',
        ]);
    }
}
