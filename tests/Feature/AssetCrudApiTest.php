<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\LocationMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetCrudApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_can_be_created_through_the_api(): void
    {
        $category = $this->createCategory();
        $location = $this->createLocation();
        $map = $this->createMap($location);

        $response = $this->postJson('/api/assets', [
            'asset_code' => 'AST-API-001',
            'name' => 'Portable Ultrasound',
            'category_id' => $category->id,
            'description' => 'Used for bedside imaging.',
            'brand' => 'SonoCare',
            'model' => 'SC-200',
            'serial_number' => 'SN-API-001',
            'barcode_value' => 'BAR-API-001',
            'qr_code_value' => 'QRA1B2C3D4',
            'rfid_tag' => 'RFID-API-001',
            'current_location_id' => $location->id,
            'current_map_id' => $map->id,
            'position_x' => 12.3456,
            'position_y' => 65.4321,
            'notes' => 'Ready for ward use.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.asset_code', 'AST-API-001')
            ->assertJsonPath('data.name', 'Portable Ultrasound')
            ->assertJsonPath('data.status', AssetStatus::Available->value)
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.current_location.id', $location->id)
            ->assertJsonPath('data.current_map.id', $map->id);

        $this->assertDatabaseHas('assets', [
            'asset_code' => 'AST-API-001',
            'status' => AssetStatus::Available->value,
            'category_id' => $category->id,
            'current_location_id' => $location->id,
            'current_map_id' => $map->id,
        ]);
    }

    public function test_assets_can_be_listed_filtered_and_viewed(): void
    {
        $diagnostics = $this->createCategory('CAT-DIAG', 'Diagnostics');
        $beds = $this->createCategory('CAT-BED', 'Beds');
        $ward = $this->createLocation('WARD-2A', 'Ward 2A');
        $storage = $this->createLocation('STORE-1', 'Storage 1', 'storage');
        $map = $this->createMap($ward);

        $matchingAsset = Asset::create([
            'asset_code' => 'AST-FILTER-001',
            'name' => 'Portable Ultrasound',
            'category_id' => $diagnostics->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $ward->id,
            'current_map_id' => $map->id,
            'barcode_value' => 'BAR-FILTER-001',
        ]);

        Asset::create([
            'asset_code' => 'AST-FILTER-002',
            'name' => 'Patient Bed',
            'category_id' => $beds->id,
            'status' => AssetStatus::Maintenance->value,
            'current_location_id' => $storage->id,
        ]);

        $this->getJson('/api/assets?search=Ultrasound&category_id='.$diagnostics->id.'&current_location_id='.$ward->id.'&status=available')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingAsset->id)
            ->assertJsonPath('data.0.category.name', 'Diagnostics')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'asset_code',
                        'name',
                        'status',
                        'category',
                        'current_location',
                        'current_map',
                    ],
                ],
                'links',
                'meta',
            ]);

        $this->getJson('/api/assets/'.$matchingAsset->id)
            ->assertOk()
            ->assertJsonPath('data.id', $matchingAsset->id)
            ->assertJsonPath('data.current_location.name', 'Ward 2A')
            ->assertJsonPath('data.current_map.name', 'Main Map');
    }

    public function test_asset_can_be_updated_through_the_api(): void
    {
        $category = $this->createCategory();
        $newCategory = $this->createCategory('CAT-LAB', 'Laboratory');
        $location = $this->createLocation();
        $asset = Asset::create([
            'asset_code' => 'AST-UPDATE-001',
            'name' => 'Old Monitor',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
        ]);

        $response = $this->patchJson('/api/assets/'.$asset->id, [
            'asset_code' => 'AST-UPDATE-001',
            'name' => 'Updated Monitor',
            'category_id' => $newCategory->id,
            'status' => AssetStatus::InUse->value,
            'current_location_id' => $location->id,
            'position_x' => 22.12,
            'position_y' => 44.34,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Monitor')
            ->assertJsonPath('data.status', AssetStatus::InUse->value)
            ->assertJsonPath('data.category.id', $newCategory->id)
            ->assertJsonPath('data.current_location.id', $location->id);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'asset_code' => 'AST-UPDATE-001',
            'name' => 'Updated Monitor',
            'category_id' => $newCategory->id,
            'status' => AssetStatus::InUse->value,
            'current_location_id' => $location->id,
        ]);
    }

    public function test_asset_can_be_soft_deleted_through_the_api(): void
    {
        $category = $this->createCategory();
        $asset = Asset::create([
            'asset_code' => 'AST-DELETE-001',
            'name' => 'Retired Pump',
            'category_id' => $category->id,
            'status' => AssetStatus::Maintenance->value,
        ]);

        $this->deleteJson('/api/assets/'.$asset->id)
            ->assertNoContent();

        $this->assertSoftDeleted('assets', [
            'id' => $asset->id,
        ]);

        $this->getJson('/api/assets/'.$asset->id)
            ->assertNotFound();
    }

    public function test_asset_create_validation_rejects_invalid_payloads(): void
    {
        $category = $this->createCategory();
        $asset = Asset::create([
            'asset_code' => 'AST-DUP-001',
            'name' => 'Existing Asset',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'barcode_value' => 'BAR-DUP-001',
            'qr_code_value' => 'ZX9Y8X7W6V',
            'rfid_tag' => 'RFID-DUP-001',
        ]);

        $this->postJson('/api/assets', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['asset_code', 'name', 'category_id']);

        $this->postJson('/api/assets', [
            'asset_code' => $asset->asset_code,
            'name' => 'Duplicate Asset',
            'category_id' => 999,
            'status' => 'lost',
            'current_location_id' => 999,
            'current_map_id' => 999,
            'barcode_value' => $asset->barcode_value,
            'qr_code_value' => $asset->qr_code_value,
            'rfid_tag' => $asset->rfid_tag,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'asset_code',
                'category_id',
                'status',
                'current_location_id',
                'current_map_id',
                'barcode_value',
                'qr_code_value',
                'rfid_tag',
            ]);
    }

    public function test_asset_qr_code_values_are_normalized_to_uppercase_and_format_validated(): void
    {
        $category = $this->createCategory();

        $this->postJson('/api/assets', [
            'asset_code' => 'AST-QR-FORMAT-001',
            'name' => 'Lowercase QR Asset',
            'category_id' => $category->id,
            'qr_code_value' => 'jt8jhfk97h',
        ])
            ->assertCreated()
            ->assertJsonPath('data.qr_code_value', 'JT8JHFK97H');

        $this->postJson('/api/assets', [
            'asset_code' => 'AST-QR-FORMAT-002',
            'name' => 'Invalid QR Asset',
            'category_id' => $category->id,
            'qr_code_value' => 'bad-format',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['qr_code_value']);
    }

    public function test_asset_update_validation_rejects_duplicate_identifiers(): void
    {
        $category = $this->createCategory();
        $existingAsset = Asset::create([
            'asset_code' => 'AST-EXISTING-001',
            'name' => 'Existing Asset',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'barcode_value' => 'BAR-EXISTING-001',
        ]);

        $asset = Asset::create([
            'asset_code' => 'AST-TARGET-001',
            'name' => 'Target Asset',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'barcode_value' => 'BAR-TARGET-001',
        ]);

        $this->patchJson('/api/assets/'.$asset->id, [
            'asset_code' => $existingAsset->asset_code,
            'barcode_value' => $existingAsset->barcode_value,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['asset_code', 'barcode_value']);

        $this->patchJson('/api/assets/'.$asset->id, [
            'asset_code' => $asset->asset_code,
            'barcode_value' => $asset->barcode_value,
            'name' => 'Target Asset With Same Identifiers',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Target Asset With Same Identifiers');
    }

    private function createCategory(string $code = 'CAT-API-001', string $name = 'Imaging'): AssetCategory
    {
        return AssetCategory::create([
            'code' => $code,
            'name' => $name,
        ]);
    }

    private function createLocation(string $code = 'LOC-API-001', string $name = 'Ward A', string $type = 'room'): Location
    {
        return Location::create([
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'floor_number' => 1,
            'is_active' => true,
        ]);
    }

    private function createMap(Location $location, string $name = 'Main Map'): LocationMap
    {
        return LocationMap::create([
            'location_id' => $location->id,
            'name' => $name,
            'image_path' => 'maps/main-floor.png',
            'image_width' => 1200,
            'image_height' => 800,
        ]);
    }
}
