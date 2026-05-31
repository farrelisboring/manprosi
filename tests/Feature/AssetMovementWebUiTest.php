<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\MovementSource;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMovement;
use App\Models\Location;
use App\Models\LocationMap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetMovementWebUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_tracking_page_loads_with_current_placement_and_history(): void
    {
        $this->signInAsRole(UserRole::Staff);
        $asset = $this->createAssetWithPlacement();
        $earlierLocation = $this->createLocation('MOVE-WEB-ROOT', 'Root');
        $latestMovement = AssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => $earlierLocation->id,
            'to_location_id' => $asset->current_location_id,
            'movement_source' => MovementSource::Manual->value,
            'reason' => 'Relocated for rounds',
            'moved_at' => '2026-05-15 11:00:00',
        ]);

        AssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => null,
            'to_location_id' => $earlierLocation->id,
            'movement_source' => MovementSource::Rfid->value,
            'moved_at' => '2026-05-14 09:00:00',
        ]);

        $this->get('/assets/'.$asset->id.'/tracking')
            ->assertOk()
            ->assertSee('Tracking Aset')
            ->assertSee($asset->currentLocation->name)
            ->assertSee($asset->currentLocation->locationMap->name)
            ->assertDontSee('Position X')
            ->assertDontSee('Position Y')
            ->assertDontSee('Movement source')
            ->assertSee($latestMovement->reason)
            ->assertSee('Record movement');
    }

    public function test_tracking_filters_apply_and_preserve_query_strings(): void
    {
        $this->signInAsRole(UserRole::Staff);
        $asset = $this->createAssetWithPlacement();
        $from = $this->createLocation('MOVE-WEB-FROM', 'North Wing');
        $other = $this->createLocation('MOVE-WEB-ALT', 'South Wing');

        AssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => $from->id,
            'to_location_id' => $asset->current_location_id,
            'movement_source' => MovementSource::Rfid->value,
            'reason' => 'Reader-detected transfer',
            'moved_at' => '2026-05-15 11:00:00',
        ]);

        AssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => $other->id,
            'to_location_id' => $asset->current_location_id,
            'movement_source' => MovementSource::Manual->value,
            'reason' => 'Manual portering move',
            'moved_at' => '2026-05-14 11:00:00',
        ]);

        $response = $this->get('/assets/'.$asset->id.'/tracking?movement_source=rfid&from_location_id='.$from->id.'&date_from=2026-05-15');

        $response
            ->assertOk()
            ->assertSee('Reader-detected transfer')
            ->assertDontSee('Manual portering move')
            ->assertSee('value="'.$from->id.'" selected', false)
            ->assertSee('value="2026-05-15"', false);
    }

    public function test_create_movement_page_renders_full_form_and_current_context(): void
    {
        $this->signInAsRole(UserRole::Nurse);
        $asset = $this->createAssetWithPlacement();
        $destination = $this->createLocation('MOVE-WEB-DEST', 'Radiology');
        $destinationMap = $this->createMap($destination, 'Radiology Map');
        $user = User::factory()->create();

        $this->get('/assets/'.$asset->id.'/movements/create')
            ->assertOk()
            ->assertSee('Form Pemindahan')
            ->assertSee($asset->currentLocation->name)
            ->assertSee($destination->name)
            ->assertSee($user->email)
            ->assertDontSee('Movement source')
            ->assertSeeInOrder(['name="moved_at"', 'required', 'type="datetime-local"'], false)
            ->assertDontSee('Position X')
            ->assertDontSee('Position Y')
            ->assertSee('max="2038-01-19T03:14"', false);
    }

    public function test_successful_movement_submission_updates_asset_and_redirects_to_tracking(): void
    {
        $this->signInAsRole(UserRole::Nurse);
        $asset = $this->createAssetWithPlacement();
        $destination = $this->createLocation('MOVE-WEB-NEW', 'Ward West');
        $destinationMap = $this->createMap($destination, 'Ward West Map');
        $user = User::factory()->create();

        $this->post('/assets/'.$asset->id.'/movements', [
            'to_location_id' => $destination->id,
            'moved_by_user_id' => $user->id,
            'reason' => 'Sent for urgent care',
            'notes' => 'Moved after triage escalation.',
            'moved_at' => '2026-05-15 12:30:00',
            'current_map_id' => $destinationMap->id,
            'position_x' => 12.125,
            'position_y' => 88.75,
        ])
            ->assertRedirect('/assets/'.$asset->id.'/tracking')
            ->assertSessionHas('status_message', 'Movement recorded successfully.');

        $asset->refresh();

        $this->assertSame($destination->id, $asset->current_location_id);
        $this->assertSame($destinationMap->id, $asset->current_map_id);
        $this->assertSame(12.125, $asset->position_x);
        $this->assertSame(88.75, $asset->position_y);
        $this->assertDatabaseHas('asset_movements', [
            'asset_id' => $asset->id,
            'to_location_id' => $destination->id,
            'moved_by_user_id' => $user->id,
            'movement_source' => MovementSource::Manual->value,
        ]);
    }

    public function test_movement_submission_without_destination_placement_clears_stale_map_data(): void
    {
        $this->signInAsRole(UserRole::Nurse);
        $asset = $this->createAssetWithPlacement();
        $destination = $this->createLocation('MOVE-WEB-CLEAR', 'Storage Annex');

        $this->post('/assets/'.$asset->id.'/movements', [
            'to_location_id' => $destination->id,
            'reason' => 'Stored after maintenance',
            'moved_at' => '2026-05-15 12:45:00',
        ])->assertRedirect('/assets/'.$asset->id.'/tracking');

        $asset->refresh();

        $this->assertSame($destination->id, $asset->current_location_id);
        $this->assertNull($asset->current_map_id);
        $this->assertNull($asset->position_x);
        $this->assertNull($asset->position_y);
    }

    public function test_same_location_movement_is_allowed_when_map_placement_changes_in_web_flow(): void
    {
        $this->signInAsRole(UserRole::Nurse);
        $asset = $this->createAssetWithPlacement();
        $replacementMap = $this->createMap($asset->currentLocation, 'Secondary Room Map');

        $this->post('/assets/'.$asset->id.'/movements', [
            'to_location_id' => $asset->current_location_id,
            'current_map_id' => $replacementMap->id,
            'moved_at' => '2026-05-15 14:15:00',
        ])->assertRedirect('/assets/'.$asset->id.'/tracking');

        $asset->refresh();

        $this->assertSame($replacementMap->id, $asset->current_map_id);
        $this->assertNull($asset->position_x);
        $this->assertNull($asset->position_y);
    }

    public function test_invalid_destination_map_combination_fails_in_web_flow(): void
    {
        $this->signInAsRole(UserRole::Nurse);
        $asset = $this->createAssetWithPlacement();
        $destination = $this->createLocation('MOVE-WEB-BAD', 'Lab East');
        $wrongLocation = $this->createLocation('MOVE-WEB-WRONG', 'Lab West');
        $wrongMap = $this->createMap($wrongLocation, 'Wrong Lab Map');

        $this->from('/assets/'.$asset->id.'/movements/create')
            ->post('/assets/'.$asset->id.'/movements', [
                'to_location_id' => $destination->id,
                'current_map_id' => $wrongMap->id,
                'moved_at' => '2026-05-15 13:45:00',
            ])
            ->assertRedirect('/assets/'.$asset->id.'/movements/create')
            ->assertSessionHasErrors('current_map_id');
    }

    public function test_movement_create_requires_moved_at_in_web_flow(): void
    {
        $this->signInAsRole(UserRole::Nurse);
        $asset = $this->createAssetWithPlacement();
        $destination = $this->createLocation('MOVE-WEB-REQ', 'Procedure Room');

        $this->from('/assets/'.$asset->id.'/movements/create')
            ->post('/assets/'.$asset->id.'/movements', [
                'to_location_id' => $destination->id,
            ])
            ->assertRedirect('/assets/'.$asset->id.'/movements/create')
            ->assertSessionHasErrors('moved_at');
    }

    public function test_out_of_range_movement_timestamp_fails_validation_in_web_flow(): void
    {
        $this->signInAsRole(UserRole::Nurse);
        $asset = $this->createAssetWithPlacement();
        $destination = $this->createLocation('MOVE-WEB-TIME', 'Overflow Ward');

        $this->from('/assets/'.$asset->id.'/movements/create')
            ->post('/assets/'.$asset->id.'/movements', [
                'to_location_id' => $destination->id,
                'reason' => 'Testing timestamp bounds',
                'moved_at' => '2222-02-22 14:02:00',
            ])
            ->assertRedirect('/assets/'.$asset->id.'/movements/create')
            ->assertSessionHasErrors('moved_at');

        $this->assertDatabaseMissing('asset_movements', [
            'asset_id' => $asset->id,
            'to_location_id' => $destination->id,
            'reason' => 'Testing timestamp bounds',
        ]);
    }

    public function test_tracking_refresh_endpoint_returns_updated_partial_and_respects_filters(): void
    {
        $this->signInAsRole(UserRole::Staff);
        $asset = $this->createAssetWithPlacement();
        $source = $this->createLocation('MOVE-WEB-SRC', 'Equipment Hub');

        AssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => $source->id,
            'to_location_id' => $asset->current_location_id,
            'movement_source' => MovementSource::Rfid->value,
            'reason' => 'Tracked by gate reader',
            'moved_at' => '2026-05-15 11:00:00',
        ]);

        AssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => null,
            'to_location_id' => $asset->current_location_id,
            'movement_source' => MovementSource::Manual->value,
            'reason' => 'Manual adjustment',
            'moved_at' => '2026-05-14 10:00:00',
        ]);

        $this->get('/assets/'.$asset->id.'/tracking/panel?movement_source=rfid')
            ->assertOk()
            ->assertSee('Tracked by gate reader')
            ->assertDontSee('Manual adjustment')
            ->assertSee('Riwayat Perpindahan');
    }

    private function createAssetWithPlacement(): Asset
    {
        $category = AssetCategory::create([
            'code' => 'MOVE-WEB-CAT',
            'name' => 'Movement Web Category',
        ]);

        $gedung = LocationMap::create([
            'name' => 'Gedung A',
            'image_path' => 'maps/gedung-a.png',
            'image_width' => 1200,
            'image_height' => 800,
        ]);
        $location = $this->createLocation('MOVE-WEB-BASE', 'Ward A', $gedung->id);
        $map = $this->createMap($location, 'Ward A Map');

        return Asset::create([
            'asset_code' => 'MOVE-WEB-ASSET',
            'name' => 'Portable Monitor',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $location->id,
            'current_map_id' => $map->id,
            'position_x' => 25.5,
            'position_y' => 50.75,
        ]);
    }

    private function createLocation(string $code, string $name, ?int $locationMapId = null): Location
    {
        return Location::create([
            'code' => $code,
            'name' => $name,
            'type' => 'room',
            'floor_number' => 1,
            'location_map_id' => $locationMapId,
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
