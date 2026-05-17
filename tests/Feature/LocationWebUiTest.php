<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationWebUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_location_pages_load(): void
    {
        $location = $this->createLocation('LOC-UI-001', 'North Tower');

        $this->get('/locations')
            ->assertOk()
            ->assertSee('Locations')
            ->assertSee($location->name);

        $this->get('/locations/create')
            ->assertOk()
            ->assertSee('New location');

        $this->get('/locations/'.$location->id)
            ->assertOk()
            ->assertSee($location->name)
            ->assertSee('Location snapshot');

        $this->get('/locations/'.$location->id.'/edit')
            ->assertOk()
            ->assertSee('Edit location')
            ->assertSee('value="'.$location->code.'"', false);
    }

    public function test_location_can_be_created_and_updated(): void
    {
        $parent = $this->createLocation('LOC-UI-010', 'Main Hospital');

        $this->post('/locations', [
            'parent_id' => $parent->id,
            'code' => 'LOC-UI-011',
            'name' => 'ICU Wing',
            'type' => 'Building',
            'floor_number' => 4,
            'description' => 'Critical care floor.',
            'is_active' => '1',
        ])->assertRedirect();

        $location = Location::query()->where('code', 'LOC-UI-011')->firstOrFail();

        $this->assertSame($parent->id, $location->parent_id);
        $this->assertSame('ICU Wing', $location->name);
        $this->assertSame('Building', $location->type);
        $this->assertSame(4, $location->floor_number);
        $this->assertTrue($location->is_active);

        $this->put('/locations/'.$location->id, [
            'parent_id' => null,
            'code' => 'LOC-UI-011',
            'name' => 'ICU and Recovery Wing',
            'type' => 'Lab',
            'floor_number' => 5,
            'description' => 'Updated notes.',
            'is_active' => '0',
        ])->assertRedirect('/locations/'.$location->id);

        $location->refresh();

        $this->assertNull($location->parent_id);
        $this->assertSame('ICU and Recovery Wing', $location->name);
        $this->assertSame('Lab', $location->type);
        $this->assertSame(5, $location->floor_number);
        $this->assertFalse($location->is_active);
    }

    public function test_location_delete_succeeds_when_no_related_rows_exist(): void
    {
        $location = $this->createLocation('LOC-UI-020', 'Archive Room');

        $this->delete('/locations/'.$location->id)
            ->assertRedirect('/locations');

        $this->assertSoftDeleted('locations', [
            'id' => $location->id,
        ]);
    }

    public function test_location_delete_is_blocked_in_ui_and_on_server_when_related_rows_exist(): void
    {
        $location = $this->createLocation('LOC-UI-030', 'Radiology');
        $this->createLocation('LOC-UI-031', 'Radiology Storage', parentId: $location->id);

        $this->get('/locations')
            ->assertOk()
            ->assertSee('data-blocked-action-message="This item cannot be deleted because related records still exist."', false);

        $this->delete('/locations/'.$location->id)
            ->assertRedirect('/locations/'.$location->id);

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'deleted_at' => null,
        ]);
    }

    public function test_location_delete_is_blocked_when_assets_reference_the_location(): void
    {
        $category = AssetCategory::create([
            'code' => 'LOC-UI-CAT',
            'name' => 'Diagnostics',
        ]);

        $location = $this->createLocation('LOC-UI-040', 'Ward A');

        Asset::create([
            'asset_code' => 'LOC-UI-ASSET-001',
            'name' => 'Portable Monitor',
            'category_id' => $category->id,
            'current_location_id' => $location->id,
        ]);

        $this->delete('/locations/'.$location->id)
            ->assertRedirect('/locations/'.$location->id)
            ->assertSessionHas('status_message', 'This item cannot be deleted because related records still exist.');

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'deleted_at' => null,
        ]);
    }

    private function createLocation(
        string $code,
        string $name,
        string $type = 'Room',
        ?int $floorNumber = 1,
        ?int $parentId = null,
        bool $isActive = true,
    ): Location {
        return Location::create([
            'parent_id' => $parentId,
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'floor_number' => $floorNumber,
            'is_active' => $isActive,
        ]);
    }
}
