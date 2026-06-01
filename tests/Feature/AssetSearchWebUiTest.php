<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\LocationMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetSearchWebUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->signInAsRole(UserRole::Staff);
    }

    public function test_search_page_loads_with_input_and_does_not_show_inventory_before_search(): void
    {
        $category = $this->createCategory();
        Asset::create([
            'asset_code' => 'SEARCH-EMPTY-001',
            'name' => 'Hidden Monitor',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
        ]);

        $this->get('/assets/search')
            ->assertOk()
            ->assertSee('Pencarian Aset')
            ->assertSee('Mulai dengan kata kunci')
            ->assertDontSee('Hidden Monitor');
    }

    public function test_search_queries_match_supported_asset_fields_and_category_name(): void
    {
        $category = $this->createCategory('CAT-SEARCH-TERM', 'Searchable Diagnostics');
        $location = $this->createLocation('LOC-SEARCH-001', 'Ward Search');
        $map = $this->createMap($location, 'Ward Search Map');

        Asset::create([
            'asset_code' => 'SEARCH-CODE-123',
            'name' => 'Acme Search Scanner',
            'category_id' => $category->id,
            'status' => AssetStatus::InUse->value,
            'serial_number' => 'SERIAL-SEARCH-555',
            'barcode_value' => 'BARCODE-SEARCH-777',
            'qr_code_value' => 'QR-SEARCH-999',
            'rfid_tag' => 'RFID-SEARCH-321',
            'current_location_id' => $location->id,
            'current_map_id' => $map->id,
            'position_x' => 1.1,
            'position_y' => 2.2,
        ]);

        Asset::create([
            'asset_code' => 'SEARCH-OTHER-001',
            'name' => 'Unrelated Bed',
            'category_id' => $this->createCategory('CAT-SEARCH-OTHER', 'General Beds')->id,
            'status' => AssetStatus::Maintenance->value,
        ]);

        foreach ([
            'SEARCH-CODE-123',
            'Acme Search Scanner',
            'SERIAL-SEARCH-555',
            'BARCODE-SEARCH-777',
            'QR-SEARCH-999',
            'RFID-SEARCH-321',
            'Searchable Diagnostics',
        ] as $term) {
            $this->get('/assets/search?search='.urlencode($term))
                ->assertOk()
                ->assertSee('Acme Search Scanner')
                ->assertDontSee('Unrelated Bed');
        }
    }

    public function test_search_filters_narrow_results_and_preserve_query_string_in_pagination(): void
    {
        $category = $this->createCategory('CAT-SEARCH-FILTER', 'Imaging');
        $otherCategory = $this->createCategory('CAT-SEARCH-OTHER', 'Beds');
        $location = $this->createLocation('LOC-SEARCH-FILTER', 'Ward A');
        $otherLocation = $this->createLocation('LOC-SEARCH-OTHER', 'Ward B');

        foreach (range(1, 16) as $number) {
            Asset::create([
                'asset_code' => 'SEARCH-FILTER-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'name' => 'Portable Ultrasound '.$number,
                'category_id' => $category->id,
                'status' => AssetStatus::Available->value,
                'current_location_id' => $location->id,
            ]);
        }

        Asset::create([
            'asset_code' => 'SEARCH-FILTER-999',
            'name' => 'Patient Bed',
            'category_id' => $otherCategory->id,
            'status' => AssetStatus::Maintenance->value,
            'current_location_id' => $otherLocation->id,
        ]);

        $response = $this->get('/assets/search?search=Ultrasound&category_id='.$category->id.'&current_location_id='.$location->id.'&status=available');

        $response
            ->assertOk()
            ->assertSee('Portable Ultrasound 1')
            ->assertDontSee('Patient Bed')
            ->assertSee('page=2', false)
            ->assertSee('search=Ultrasound', false)
            ->assertSee('category_id='.$category->id, false)
            ->assertSee('current_location_id='.$location->id, false)
            ->assertSee('status=available', false);
    }

    public function test_search_results_include_actions_and_no_results_state(): void
    {
        $category = $this->createCategory();
        $location = $this->createLocation('LOC-SEARCH-ACTION', 'Ward Action');
        $asset = Asset::create([
            'asset_code' => 'SEARCH-ACTION-001',
            'name' => 'Portable Ventilator',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $location->id,
        ]);

        $this->get('/assets/search?search=Ventilator')
            ->assertOk()
            ->assertSee('Portable Ventilator')
            ->assertSee('/assets/'.$asset->id, false)
            ->assertSee('/assets/'.$asset->id.'/tracking', false);

        $this->get('/assets/search?search=NoMatchHere')
            ->assertOk()
            ->assertSee('Tidak ada aset yang cocok dengan "NoMatchHere" untuk filter saat ini.', false);
    }

    private function createCategory(string $code = 'CAT-SEARCH', string $name = 'Imaging'): AssetCategory
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
