<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\LocationMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LocationWebUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->signInAsRole(UserRole::Manager);
    }

    public function test_location_pages_load(): void
    {
        $gedung = $this->createGedung('North Tower');
        $location = $this->createLocation('LOC-UI-001', 'North Tower Room', locationMapId: $gedung->id);

        $this->get('/locations')
            ->assertOk()
            ->assertSee('Ruangan')
            ->assertSee($location->name)
            ->assertSee($gedung->name);

        $this->get('/locations/create')
            ->assertOk()
            ->assertSee('Ruangan Baru');

        $this->get('/locations/'.$location->id)
            ->assertOk()
            ->assertSee($location->name)
            ->assertSee($gedung->name)
            ->assertSee('Ringkasan Ruangan');

        $this->get('/locations/'.$location->id.'/edit')
            ->assertOk()
            ->assertSee('Edit location')
            ->assertSee('value="'.$location->code.'"', false);
    }

    public function test_location_can_be_created_and_updated_with_optional_gedung_link(): void
    {
        $gedung = $this->createGedung('Gedung A');
        $otherGedung = $this->createGedung('Gedung B');
        $initialDenahBytes = $this->fakePngBytes();
        $denah = UploadedFile::fake()->createWithContent('denah-awal.png', $initialDenahBytes);

        $this->post('/locations', [
            'location_map_id' => $gedung->id,
            'code' => 'LOC-UI-011',
            'name' => 'ICU Wing',
            'type' => 'Building',
            'floor_number' => 4,
            'description' => 'Critical care floor.',
            'denah_image' => $denah,
            'is_active' => '1',
        ])->assertRedirect();

        $location = Location::query()->where('code', 'LOC-UI-011')->firstOrFail();

        $this->assertSame($gedung->id, $location->location_map_id);
        $this->assertSame('ICU Wing', $location->name);
        $this->assertSame('Building', $location->type);
        $this->assertSame(4, $location->floor_number);
        $this->assertTrue($location->is_active);
        $this->assertSame('image/png', $location->denah_image_mime_type);
        $this->assertSame($initialDenahBytes, $location->denah_image_data);

        $oldDenahData = $location->denah_image_data;
        $newDenahBytes = $this->fakePngBytes('second');
        $newDenah = UploadedFile::fake()->createWithContent('denah-baru.png', $newDenahBytes);

        $this->put('/locations/'.$location->id, [
            'location_map_id' => $otherGedung->id,
            'code' => 'LOC-UI-011',
            'name' => 'ICU and Recovery Wing',
            'type' => 'Lab',
            'floor_number' => 5,
            'description' => 'Updated notes.',
            'denah_image' => $newDenah,
            'is_active' => '0',
        ])->assertRedirect('/locations/'.$location->id);

        $location->refresh();

        $this->assertSame($otherGedung->id, $location->location_map_id);
        $this->assertSame('ICU and Recovery Wing', $location->name);
        $this->assertSame('Lab', $location->type);
        $this->assertSame(5, $location->floor_number);
        $this->assertFalse($location->is_active);
        $this->assertNotSame($oldDenahData, $location->denah_image_data);
        $this->assertSame('image/png', $location->denah_image_mime_type);
        $this->assertSame($newDenahBytes, $location->denah_image_data);
    }

    public function test_location_delete_succeeds_when_no_related_rows_exist(): void
    {
        $location = $this->createLocation('LOC-UI-020', 'Archive Room');
        $location->update([
            'denah_image_data' => $this->fakePngBytes('delete'),
            'denah_image_mime_type' => 'image/png',
        ]);

        $this->delete('/locations/'.$location->id)
            ->assertRedirect('/locations');

        $this->assertSoftDeleted('locations', [
            'id' => $location->id,
        ]);
    }

    public function test_location_delete_is_not_blocked_by_a_linked_gedung(): void
    {
        $gedung = $this->createGedung('Gedung Netral');
        $location = $this->createLocation('LOC-UI-021', 'Archive Room', locationMapId: $gedung->id);

        $this->delete('/locations/'.$location->id)
            ->assertRedirect('/locations');

        $this->assertSoftDeleted('locations', [
            'id' => $location->id,
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

    public function test_denah_route_returns_image_content_when_present(): void
    {
        $bytes = $this->fakePngBytes('route');
        $location = $this->createLocation('LOC-UI-050', 'Denah Route Room');
        $location->update([
            'denah_image_data' => $bytes,
            'denah_image_mime_type' => 'image/png',
        ]);

        $this->get('/locations/'.$location->id.'/denah')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent($bytes);
    }

    public function test_denah_route_returns_not_found_when_image_is_missing(): void
    {
        $location = $this->createLocation('LOC-UI-051', 'No Denah Room');

        $this->get('/locations/'.$location->id.'/denah')
            ->assertNotFound();
    }

    private function createLocation(
        string $code,
        string $name,
        string $type = 'Room',
        ?int $floorNumber = 1,
        ?int $locationMapId = null,
        bool $isActive = true,
    ): Location {
        return Location::create([
            'location_map_id' => $locationMapId,
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'floor_number' => $floorNumber,
            'is_active' => $isActive,
        ]);
    }

    private function createGedung(string $name): LocationMap
    {
        return LocationMap::create([
            'name' => $name,
            'notes' => 'Catatan uji.',
        ]);
    }

    private function fakePngBytes(string $seed = 'base'): string
    {
        $base64BySeed = [
            'base' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WnR0P8AAAAASUVORK5CYII=',
            'second' => 'iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAQAAAD8fJRsAAAAD0lEQVR42mNk+M/QwMAAAAMBAQAYgmWQAAAAAElFTkSuQmCC',
            'delete' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScbJx0AAAAASUVORK5CYII=',
            'route' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP4zwAAAgEBAQot6QAAAABJRU5ErkJggg==',
        ];

        return base64_decode($base64BySeed[$seed], true);
    }
}
