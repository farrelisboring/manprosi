<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\MovementSource;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMovement;
use App\Models\Location;
use App\Models\LocationMap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetMovementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_movement_can_be_created_and_syncs_asset_current_location(): void
    {
        $category = $this->createCategory();
        $fromLocation = $this->createLocation('ER-01', 'Emergency Room');
        $toLocation = $this->createLocation('WARD-01', 'Ward 1');
        $destinationMap = $this->createMap($toLocation, 'Ward Map');
        $user = User::factory()->create();
        $asset = $this->createAsset($category, [
            'current_location_id' => $fromLocation->id,
        ]);

        $response = $this->postJson('/api/assets/'.$asset->id.'/movements', [
            'to_location_id' => $toLocation->id,
            'moved_by_user_id' => $user->id,
            'movement_source' => MovementSource::QrCode->value,
            'reason' => 'Needed for patient care',
            'notes' => 'Moved by nurse station request.',
            'moved_at' => '2026-05-10 09:30:00',
            'current_map_id' => $destinationMap->id,
            'position_x' => 15.25,
            'position_y' => 44.5,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.asset.id', $asset->id)
            ->assertJsonPath('data.from_location.id', $fromLocation->id)
            ->assertJsonPath('data.to_location.id', $toLocation->id)
            ->assertJsonPath('data.moved_by_user.id', $user->id)
            ->assertJsonPath('data.movement_source', MovementSource::QrCode->value)
            ->assertJsonPath('data.reason', 'Needed for patient care');

        $this->assertDatabaseHas('asset_movements', [
            'asset_id' => $asset->id,
            'from_location_id' => $fromLocation->id,
            'to_location_id' => $toLocation->id,
            'moved_by_user_id' => $user->id,
            'movement_source' => MovementSource::QrCode->value,
            'reason' => 'Needed for patient care',
        ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'current_location_id' => $toLocation->id,
            'current_map_id' => $destinationMap->id,
        ]);

        $asset->refresh();
        $this->assertSame(15.25, $asset->position_x);
        $this->assertSame(44.5, $asset->position_y);
    }

    public function test_first_asset_placement_can_have_null_from_location(): void
    {
        $category = $this->createCategory();
        $toLocation = $this->createLocation('WARD-02', 'Ward 2');
        $asset = $this->createAsset($category);

        $response = $this->postJson('/api/assets/'.$asset->id.'/movements', [
            'to_location_id' => $toLocation->id,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.from_location', null)
            ->assertJsonPath('data.to_location.id', $toLocation->id)
            ->assertJsonPath('data.movement_source', MovementSource::Manual->value);

        $this->assertDatabaseHas('asset_movements', [
            'asset_id' => $asset->id,
            'from_location_id' => null,
            'to_location_id' => $toLocation->id,
            'movement_source' => MovementSource::Manual->value,
        ]);
    }

    public function test_movement_without_map_payload_clears_stale_asset_map_placement(): void
    {
        $category = $this->createCategory();
        $fromLocation = $this->createLocation('ICU-01', 'ICU');
        $toLocation = $this->createLocation('LAB-01', 'Lab');
        $oldMap = $this->createMap($fromLocation, 'ICU Map');
        $asset = $this->createAsset($category, [
            'current_location_id' => $fromLocation->id,
            'current_map_id' => $oldMap->id,
            'position_x' => 10,
            'position_y' => 20,
        ]);

        $this->postJson('/api/assets/'.$asset->id.'/movements', [
            'to_location_id' => $toLocation->id,
        ])->assertCreated();

        $asset->refresh();

        $this->assertSame($toLocation->id, $asset->current_location_id);
        $this->assertNull($asset->current_map_id);
        $this->assertNull($asset->position_x);
        $this->assertNull($asset->position_y);
    }

    public function test_same_location_movement_is_allowed_when_map_placement_changes(): void
    {
        $category = $this->createCategory();
        $location = $this->createLocation('RAD-01', 'Radiology');
        $map = $this->createMap($location, 'Radiology Map');
        $asset = $this->createAsset($category, [
            'current_location_id' => $location->id,
        ]);

        $this->postJson('/api/assets/'.$asset->id.'/movements', [
            'to_location_id' => $location->id,
            'current_map_id' => $map->id,
            'position_x' => 30.75,
            'position_y' => 12.5,
            'reason' => 'Placed on the digital map.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.from_location.id', $location->id)
            ->assertJsonPath('data.to_location.id', $location->id);

        $asset->refresh();

        $this->assertSame($map->id, $asset->current_map_id);
        $this->assertSame(30.75, $asset->position_x);
        $this->assertSame(12.5, $asset->position_y);
    }

    public function test_asset_movement_history_can_be_listed_filtered_and_viewed(): void
    {
        $category = $this->createCategory();
        $asset = $this->createAsset($category);
        $otherAsset = $this->createAsset($category, ['asset_code' => 'AST-MOVE-OTHER']);
        $source = $this->createLocation('SRC-01', 'Source');
        $ward = $this->createLocation('WARD-03', 'Ward 3');
        $storage = $this->createLocation('STORE-03', 'Storage 3');

        $olderMovement = AssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => $source->id,
            'to_location_id' => $ward->id,
            'movement_source' => MovementSource::Manual->value,
            'moved_at' => '2026-05-09 08:00:00',
        ]);

        $newerMovement = AssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => $ward->id,
            'to_location_id' => $storage->id,
            'movement_source' => MovementSource::Rfid->value,
            'moved_at' => '2026-05-10 10:00:00',
        ]);

        AssetMovement::create([
            'asset_id' => $otherAsset->id,
            'from_location_id' => $source->id,
            'to_location_id' => $storage->id,
            'movement_source' => MovementSource::Rfid->value,
            'moved_at' => '2026-05-10 11:00:00',
        ]);

        $this->getJson('/api/assets/'.$asset->id.'/movements')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newerMovement->id)
            ->assertJsonPath('data.1.id', $olderMovement->id)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'movement_source',
                        'asset',
                        'from_location',
                        'to_location',
                        'moved_by_user',
                        'moved_at',
                    ],
                ],
                'links',
                'meta',
            ]);

        $query = http_build_query([
            'movement_source' => MovementSource::Rfid->value,
            'from_location_id' => $ward->id,
            'to_location_id' => $storage->id,
            'date_from' => '2026-05-10 00:00:00',
            'date_to' => '2026-05-10 23:59:59',
        ]);

        $this->getJson('/api/assets/'.$asset->id.'/movements?'.$query)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $newerMovement->id);

        $this->getJson('/api/asset-movements/'.$olderMovement->id)
            ->assertOk()
            ->assertJsonPath('data.id', $olderMovement->id)
            ->assertJsonPath('data.asset.id', $asset->id)
            ->assertJsonPath('data.from_location.id', $source->id)
            ->assertJsonPath('data.to_location.id', $ward->id);
    }

    public function test_movement_creation_validation_rejects_invalid_payloads(): void
    {
        $category = $this->createCategory();
        $location = $this->createLocation('WARD-04', 'Ward 4');
        $otherLocation = $this->createLocation('WARD-05', 'Ward 5');
        $wrongMap = $this->createMap($otherLocation, 'Wrong Map');
        $asset = $this->createAsset($category, [
            'current_location_id' => $location->id,
        ]);

        $this->postJson('/api/assets/'.$asset->id.'/movements', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['to_location_id']);

        $this->postJson('/api/assets/'.$asset->id.'/movements', [
            'to_location_id' => 999,
            'moved_by_user_id' => 999,
            'movement_source' => 'invalid_source',
            'current_map_id' => 999,
            'position_x' => 1,
            'position_y' => 2,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['to_location_id', 'moved_by_user_id', 'movement_source', 'current_map_id']);

        $this->postJson('/api/assets/'.$asset->id.'/movements', [
            'to_location_id' => $location->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['to_location_id']);

        $this->postJson('/api/assets/'.$asset->id.'/movements', [
            'to_location_id' => $location->id,
            'current_map_id' => $wrongMap->id,
            'position_x' => 1,
            'position_y' => 2,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_map_id']);

        $this->postJson('/api/assets/'.$asset->id.'/movements', [
            'to_location_id' => $otherLocation->id,
            'current_map_id' => $wrongMap->id,
            'position_x' => 1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['position_y']);
    }

    private function createCategory(): AssetCategory
    {
        return AssetCategory::create([
            'code' => 'CAT-MOVE',
            'name' => 'Movement Test Category',
        ]);
    }

    private function createAsset(AssetCategory $category, array $overrides = []): Asset
    {
        return Asset::create(array_merge([
            'asset_code' => 'AST-MOVE-001',
            'name' => 'Mobile Ventilator',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
        ], $overrides));
    }

    private function createLocation(string $code, string $name, string $type = 'room'): Location
    {
        return Location::create([
            'code' => $code,
            'name' => $name,
            'type' => $type,
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
