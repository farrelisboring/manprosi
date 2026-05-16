<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\LocationMap;
use App\Services\QrCodeValueGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetQrLabelApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_label_can_be_generated_for_an_existing_asset(): void
    {
        [$asset, $category, $location, $map] = $this->createAssetWithContext();

        $response = $this->postJson('/api/assets/'.$asset->id.'/qr-label');

        $response
            ->assertCreated()
            ->assertJsonPath('data.asset.id', $asset->id)
            ->assertJsonPath('data.asset.asset_code', 'AST-QR-001')
            ->assertJsonPath('data.asset.name', 'Portable Ultrasound')
            ->assertJsonPath('data.asset.status', AssetStatus::Available->value)
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.current_location.id', $location->id)
            ->assertJsonPath('data.current_map.id', $map->id)
            ->assertJsonPath('data.label_state.has_qr_label', true)
            ->assertJsonPath('data.label_state.has_map_placement', true)
            ->assertJsonPath('data.label_state.has_printable_code', true);

        $qrCodeValue = $response->json('data.qr_code_value');

        $this->assertMatchesRegularExpression('/^[A-Z0-9]{10}$/', $qrCodeValue);
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'qr_code_value' => $qrCodeValue,
        ]);
    }

    public function test_generating_a_second_qr_label_for_the_same_asset_returns_conflict(): void
    {
        [$asset] = $this->createAssetWithContext([
            'qr_code_value' => 'AB12CD34EF',
        ]);

        $this->postJson('/api/assets/'.$asset->id.'/qr-label')
            ->assertConflict()
            ->assertJsonPath('message', 'Asset already has a QR label.');
    }

    public function test_qr_label_can_be_read_by_asset_id_and_resolved_by_code(): void
    {
        [$asset] = $this->createAssetWithContext([
            'qr_code_value' => 'ZX90QP12LM',
        ]);

        $this->getJson('/api/assets/'.$asset->id.'/qr-label')
            ->assertOk()
            ->assertJsonPath('data.qr_code_value', $asset->qr_code_value)
            ->assertJsonPath('data.asset.id', $asset->id)
            ->assertJsonStructure([
                'data' => [
                    'qr_code_value',
                    'asset' => ['id', 'asset_code', 'name', 'status', 'brand', 'model', 'serial_number'],
                    'category' => ['id', 'code', 'name'],
                    'current_location' => ['id', 'code', 'name', 'type', 'floor_number'],
                    'current_map' => ['id', 'name', 'image_path', 'image_width', 'image_height'],
                    'label_state' => ['has_qr_label', 'has_map_placement', 'has_printable_code'],
                    'updated_at',
                ],
            ]);

        $this->getJson('/api/qr-labels/'.$asset->qr_code_value)
            ->assertOk()
            ->assertJsonPath('data.asset.id', $asset->id)
            ->assertJsonPath('data.qr_code_value', $asset->qr_code_value);

        $this->getJson('/api/qr-labels/'.strtolower($asset->qr_code_value))
            ->assertOk()
            ->assertJsonPath('data.asset.id', $asset->id)
            ->assertJsonPath('data.qr_code_value', $asset->qr_code_value);
    }

    public function test_qr_label_lookup_handles_missing_labels_invalid_codes_and_unknown_codes(): void
    {
        [$asset] = $this->createAssetWithContext();

        $this->getJson('/api/assets/'.$asset->id.'/qr-label')
            ->assertNotFound()
            ->assertJsonPath('message', 'Asset does not have a QR label.');

        $this->getJson('/api/qr-labels/not-a-code')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['qr_code_value']);

        $this->getJson('/api/qr-labels/ZZ99YY88XX')
            ->assertNotFound()
            ->assertJsonPath('message', 'QR label was not found.');
    }

    public function test_qr_label_can_be_regenerated_with_confirmation(): void
    {
        [$asset] = $this->createAssetWithContext([
            'qr_code_value' => 'REGEN12345',
        ]);
        $oldQrCodeValue = $asset->qr_code_value;

        $this->patchJson('/api/assets/'.$asset->id.'/qr-label')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['confirm_regeneration']);

        $response = $this->patchJson('/api/assets/'.$asset->id.'/qr-label', [
            'confirm_regeneration' => true,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.asset.id', $asset->id);

        $newQrCodeValue = $response->json('data.qr_code_value');

        $this->assertMatchesRegularExpression('/^[A-Z0-9]{10}$/', $newQrCodeValue);
        $this->assertNotSame($oldQrCodeValue, $newQrCodeValue);
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'qr_code_value' => $newQrCodeValue,
        ]);
    }

    public function test_qr_label_can_be_deleted_with_confirmation(): void
    {
        [$asset] = $this->createAssetWithContext([
            'qr_code_value' => 'DELETE1234',
        ]);
        $oldQrCodeValue = $asset->qr_code_value;

        $this->deleteJson('/api/assets/'.$asset->id.'/qr-label')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['confirm_deletion']);

        $this->deleteJson('/api/assets/'.$asset->id.'/qr-label', [
            'confirm_deletion' => true,
        ])->assertNoContent();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'qr_code_value' => null,
        ]);

        $this->getJson('/api/qr-labels/'.$oldQrCodeValue)
            ->assertNotFound()
            ->assertJsonPath('message', 'QR label was not found.');
    }

    public function test_regeneration_and_deletion_require_an_existing_qr_label(): void
    {
        [$asset] = $this->createAssetWithContext();

        $this->patchJson('/api/assets/'.$asset->id.'/qr-label', [
            'confirm_regeneration' => true,
        ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Asset does not have a QR label.');

        $this->deleteJson('/api/assets/'.$asset->id.'/qr-label', [
            'confirm_deletion' => true,
        ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Asset does not have a QR label.');
    }

    public function test_generator_creates_ten_character_uppercase_codes(): void
    {
        $value = app(QrCodeValueGenerator::class)->generate();

        $this->assertSame(QrCodeValueGenerator::LENGTH, strlen($value));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{10}$/', $value);
    }

    private function createAssetWithContext(array $assetOverrides = []): array
    {
        $category = AssetCategory::create([
            'code' => 'IMG',
            'name' => 'Imaging Equipment',
        ]);

        $location = Location::create([
            'code' => 'WARD-2A',
            'name' => 'Ward 2A',
            'type' => 'room',
            'floor_number' => 2,
            'is_active' => true,
        ]);

        $map = LocationMap::create([
            'location_id' => $location->id,
            'name' => 'Second Floor Map',
            'image_path' => 'maps/second-floor.png',
            'image_width' => 1200,
            'image_height' => 800,
        ]);

        $asset = Asset::create(array_merge([
            'asset_code' => 'AST-QR-001',
            'name' => 'Portable Ultrasound',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'brand' => 'SonoCare',
            'model' => 'SC-200',
            'serial_number' => 'SN-9821',
            'current_location_id' => $location->id,
            'current_map_id' => $map->id,
            'position_x' => 12.3456,
            'position_y' => 65.4321,
        ], $assetOverrides));

        return [$asset, $category, $location, $map];
    }
}
