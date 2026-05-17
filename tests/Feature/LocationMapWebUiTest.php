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
        $locationMap = $this->createGedung('Emergency Tower');
        $location = $this->createLocation('MAP-UI-001', 'Emergency Room', locationMapId: $locationMap->id);

        $this->get('/location-maps')
            ->assertOk()
            ->assertSee('Gedung')
            ->assertSee($locationMap->name);

        $this->get('/location-maps/create')
            ->assertOk()
            ->assertSee('Tambahkan Gedung');

        $this->get('/location-maps/'.$locationMap->id)
            ->assertOk()
            ->assertSee($locationMap->name)
            ->assertSee($location->name);

        $this->get('/location-maps/'.$locationMap->id.'/edit')
            ->assertOk()
            ->assertSee('Edit Gedung')
            ->assertSee('value="'.$locationMap->name.'"', false);
    }

    public function test_location_map_can_be_created_and_updated_without_room_assignment(): void
    {
        $this->post('/location-maps', [
            'name' => 'Surgery Tower',
            'notes' => 'North side.',
        ])->assertRedirect();

        $locationMap = LocationMap::query()->where('name', 'Surgery Tower')->firstOrFail();

        $this->assertNull($locationMap->location_id);
        $this->assertNull($locationMap->image_path);
        $this->assertNull($locationMap->image_width);
        $this->assertNull($locationMap->image_height);

        $this->put('/location-maps/'.$locationMap->id, [
            'name' => 'Recovery Tower',
            'notes' => 'Updated notes.',
        ])->assertRedirect('/location-maps/'.$locationMap->id);

        $locationMap->refresh();

        $this->assertSame('Recovery Tower', $locationMap->name);
        $this->assertSame('Updated notes.', $locationMap->notes);
    }

    public function test_location_map_delete_succeeds_when_unused(): void
    {
        $locationMap = $this->createGedung('Storage Tower');

        $this->delete('/location-maps/'.$locationMap->id)
            ->assertRedirect('/location-maps');

        $this->assertDatabaseMissing('location_maps', [
            'id' => $locationMap->id,
        ]);
    }

    public function test_location_map_delete_is_blocked_when_rooms_reference_the_gedung(): void
    {
        $locationMap = $this->createGedung('NICU Tower');
        $this->createLocation('MAP-UI-030', 'NICU Room', locationMapId: $locationMap->id);

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

    public function test_location_map_delete_is_blocked_when_assets_reference_the_map(): void
    {
        $category = AssetCategory::create([
            'code' => 'MAP-UI-CAT',
            'name' => 'Respiratory',
        ]);

        $locationMap = $this->createGedung('Asset Tower');
        $location = $this->createLocation('MAP-UI-031', 'Asset Room');

        Asset::create([
            'asset_code' => 'MAP-UI-ASSET-001',
            'name' => 'Ventilator',
            'category_id' => $category->id,
            'current_location_id' => $location->id,
            'current_map_id' => $locationMap->id,
        ]);

        $this->delete('/location-maps/'.$locationMap->id)
            ->assertRedirect('/location-maps/'.$locationMap->id)
            ->assertSessionHas('status_message', 'This item cannot be deleted because related records still exist.');
    }

    private function createLocation(
        string $code,
        string $name,
        string $type = 'Room',
        ?int $floorNumber = 1,
        ?int $locationMapId = null,
    ): Location {
        return Location::create([
            'location_map_id' => $locationMapId,
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'floor_number' => $floorNumber,
            'is_active' => true,
        ]);
    }

    private function createGedung(string $name): LocationMap
    {
        return LocationMap::create([
            'name' => $name,
            'image_path' => null,
            'image_width' => null,
            'image_height' => null,
            'notes' => 'Test notes.',
        ]);
    }
}
