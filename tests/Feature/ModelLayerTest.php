<?php

namespace Tests\Feature;

use App\Enums\AlertStatus;
use App\Enums\AlertType;
use App\Enums\AssetStatus;
use App\Enums\DamageSeverity;
use App\Enums\DamageStatus;
use App\Enums\GeofenceRuleType;
use App\Enums\MovementSource;
use App\Enums\RepairUpdateType;
use App\Enums\TrackingEventType;
use App\Enums\TrackingSource;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetAlert;
use App\Models\AssetCategory;
use App\Models\AssetGeofence;
use App\Models\AssetMovement;
use App\Models\AssetTrackingEvent;
use App\Models\DamageReport;
use App\Models\Location;
use App\Models\LocationMap;
use App\Models\RepairUpdate;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_expose_expected_casts_and_helpers(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $nurse = User::factory()->create(['role' => UserRole::Nurse->value]);
        $category = $this->createCategory();
        $location = $this->createLocation();
        $map = $this->createMap($location);

        $asset = Asset::create([
            'asset_code' => 'AST-001',
            'name' => 'Infusion Pump',
            'category_id' => $category->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $location->id,
            'current_map_id' => $map->id,
            'position_x' => 12.3456,
            'position_y' => 65.4321,
            'qr_code_value' => 'QRCODE1234',
            'rfid_tag' => 'RFID-001',
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        $movement = AssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => null,
            'to_location_id' => $location->id,
            'moved_by_user_id' => $nurse->id,
            'movement_source' => MovementSource::Manual->value,
            'moved_at' => '2026-05-04 08:00:00',
        ]);

        $trackingEvent = AssetTrackingEvent::create([
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'source' => TrackingSource::Rfid->value,
            'event_type' => TrackingEventType::Detected->value,
            'payload' => ['reader' => 'north-gate'],
            'detected_at' => '2026-05-04 08:05:00',
        ]);

        $geofence = AssetGeofence::create([
            'name' => 'Ward A Perimeter',
            'asset_id' => $asset->id,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'rule_type' => GeofenceRuleType::MustStayInside->value,
            'is_active' => true,
        ]);

        $alert = AssetAlert::create([
            'asset_id' => $asset->id,
            'geofence_id' => $geofence->id,
            'location_id' => $location->id,
            'tracking_event_id' => $trackingEvent->id,
            'alert_type' => AlertType::GeofenceBreach->value,
            'message' => 'Asset left the assigned area.',
            'status' => AlertStatus::New->value,
            'triggered_at' => '2026-05-04 08:10:00',
        ]);

        $damageReport = DamageReport::create([
            'asset_id' => $asset->id,
            'reported_by_user_id' => $nurse->id,
            'location_id' => $location->id,
            'title' => 'Cracked display',
            'description' => 'The device display is visibly cracked.',
            'severity' => DamageSeverity::Medium->value,
            'status' => DamageStatus::Reported->value,
            'reported_at' => '2026-05-04 09:00:00',
        ]);

        $repairUpdate = RepairUpdate::create([
            'damage_report_id' => $damageReport->id,
            'updated_by_user_id' => $manager->id,
            'update_type' => RepairUpdateType::Inspection->value,
            'status_after' => DamageStatus::InProgress->value,
            'logged_at' => '2026-05-04 10:00:00',
        ]);

        $optionalAlert = AssetAlert::create([
            'asset_id' => $asset->id,
            'alert_type' => AlertType::GeofenceBreach->value,
            'message' => 'Optional relations can be empty.',
            'status' => AlertStatus::Resolved->value,
            'triggered_at' => '2026-05-04 11:00:00',
        ]);

        $this->assertSame(UserRole::Manager, $manager->role);
        $this->assertTrue($manager->isManager());
        $this->assertTrue($nurse->isNurse());
        $this->assertFalse($nurse->isStaff());

        $this->assertSame(AssetStatus::Available, $asset->status);
        $this->assertIsFloat($asset->position_x);
        $this->assertTrue($asset->hasMapPlacement());
        $this->assertTrue($asset->hasRfid());
        $this->assertTrue($asset->hasPrintableCode());

        $this->assertSame(MovementSource::Manual, $movement->movement_source);
        $this->assertSame(TrackingSource::Rfid, $trackingEvent->source);
        $this->assertSame(TrackingEventType::Detected, $trackingEvent->event_type);
        $this->assertSame(['reader' => 'north-gate'], $trackingEvent->payload);
        $this->assertSame(GeofenceRuleType::MustStayInside, $geofence->rule_type);
        $this->assertTrue($geofence->is_active);
        $this->assertSame(AlertType::GeofenceBreach, $alert->alert_type);
        $this->assertSame(AlertStatus::New, $alert->status);
        $this->assertSame(DamageSeverity::Medium, $damageReport->severity);
        $this->assertSame(DamageStatus::Reported, $damageReport->status);
        $this->assertSame(RepairUpdateType::Inspection, $repairUpdate->update_type);
        $this->assertSame(DamageStatus::InProgress, $repairUpdate->status_after);

        $this->assertContains(SoftDeletes::class, class_uses_recursive(AssetCategory::class));
        $this->assertContains(SoftDeletes::class, class_uses_recursive(Location::class));
        $this->assertContains(SoftDeletes::class, class_uses_recursive(Asset::class));

        $this->assertNull($optionalAlert->geofence);
        $this->assertNull($optionalAlert->location);
        $this->assertNull($optionalAlert->trackingEvent);
        $this->assertNull($optionalAlert->acknowledgedByUser);
    }

    public function test_relationships_resolve_across_the_domain_model_layer(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $nurse = User::factory()->create(['role' => UserRole::Nurse->value]);
        $category = $this->createCategory('CAT-REL', 'Monitoring');
        $parentLocation = $this->createLocation('BLDG-A', 'Building A', 'building');
        $roomLocation = $this->createLocation('ROOM-101', 'Room 101', 'room', $parentLocation);
        $map = $this->createMap($roomLocation, 'Ward Layout');

        $asset = Asset::create([
            'asset_code' => 'AST-REL-1',
            'name' => 'ECG Monitor',
            'category_id' => $category->id,
            'status' => AssetStatus::InUse->value,
            'current_location_id' => $roomLocation->id,
            'current_map_id' => $map->id,
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        $movement = AssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => $parentLocation->id,
            'to_location_id' => $roomLocation->id,
            'moved_by_user_id' => $nurse->id,
            'movement_source' => MovementSource::Manual->value,
            'moved_at' => '2026-05-04 12:00:00',
        ]);

        $trackingEvent = AssetTrackingEvent::create([
            'asset_id' => $asset->id,
            'location_id' => $roomLocation->id,
            'source' => TrackingSource::Manual->value,
            'event_type' => TrackingEventType::Moved->value,
            'detected_at' => '2026-05-04 12:05:00',
        ]);

        $geofence = AssetGeofence::create([
            'name' => 'Monitoring Zone',
            'asset_id' => $asset->id,
            'category_id' => $category->id,
            'location_id' => $roomLocation->id,
            'rule_type' => GeofenceRuleType::MustStayInside->value,
            'is_active' => true,
        ]);

        $alert = AssetAlert::create([
            'asset_id' => $asset->id,
            'geofence_id' => $geofence->id,
            'location_id' => $roomLocation->id,
            'tracking_event_id' => $trackingEvent->id,
            'alert_type' => AlertType::GeofenceBreach->value,
            'message' => 'Geofence triggered.',
            'status' => AlertStatus::Acknowledged->value,
            'acknowledged_by_user_id' => $manager->id,
            'triggered_at' => '2026-05-04 12:10:00',
        ]);

        $damageReport = DamageReport::create([
            'asset_id' => $asset->id,
            'reported_by_user_id' => $nurse->id,
            'location_id' => $roomLocation->id,
            'title' => 'Loose cable',
            'description' => 'A power cable connection is loose.',
            'severity' => DamageSeverity::Low->value,
            'status' => DamageStatus::InProgress->value,
            'reported_at' => '2026-05-04 13:00:00',
        ]);

        $repairUpdate = RepairUpdate::create([
            'damage_report_id' => $damageReport->id,
            'updated_by_user_id' => $manager->id,
            'update_type' => RepairUpdateType::Repair->value,
            'status_after' => DamageStatus::Resolved->value,
            'logged_at' => '2026-05-04 14:00:00',
        ]);

        $this->assertTrue($roomLocation->parent->is($parentLocation));
        $this->assertTrue($parentLocation->children->first()->is($roomLocation));
        $this->assertTrue($roomLocation->maps->first()->is($map));
        $this->assertTrue($map->assets->first()->is($asset));

        $this->assertTrue($asset->category->is($category));
        $this->assertTrue($asset->currentLocation->is($roomLocation));
        $this->assertTrue($asset->currentMap->is($map));
        $this->assertTrue($asset->creator->is($manager));
        $this->assertTrue($asset->updater->is($manager));
        $this->assertTrue($asset->movements->first()->is($movement));
        $this->assertTrue($asset->trackingEvents->first()->is($trackingEvent));
        $this->assertTrue($asset->geofences->first()->is($geofence));
        $this->assertTrue($asset->alerts->first()->is($alert));
        $this->assertTrue($asset->damageReports->first()->is($damageReport));

        $this->assertTrue($movement->fromLocation->is($parentLocation));
        $this->assertTrue($movement->toLocation->is($roomLocation));
        $this->assertTrue($movement->movedByUser->is($nurse));

        $this->assertTrue($trackingEvent->asset->is($asset));
        $this->assertTrue($trackingEvent->location->is($roomLocation));
        $this->assertTrue($trackingEvent->alerts->first()->is($alert));

        $this->assertTrue($geofence->asset->is($asset));
        $this->assertTrue($geofence->category->is($category));
        $this->assertTrue($geofence->location->is($roomLocation));
        $this->assertTrue($geofence->alerts->first()->is($alert));

        $this->assertTrue($alert->acknowledgedByUser->is($manager));
        $this->assertTrue($alert->trackingEvent->is($trackingEvent));

        $this->assertTrue($damageReport->reportedByUser->is($nurse));
        $this->assertTrue($damageReport->location->is($roomLocation));
        $this->assertTrue($damageReport->repairUpdates->first()->is($repairUpdate));

        $this->assertTrue($repairUpdate->damageReport->is($damageReport));
        $this->assertTrue($repairUpdate->updatedByUser->is($manager));

        $this->assertCount(1, $manager->createdAssets);
        $this->assertCount(1, $manager->updatedAssets);
        $this->assertCount(1, $manager->acknowledgedAlerts);
        $this->assertCount(1, $manager->repairUpdates);
        $this->assertCount(1, $nurse->recordedMovements);
        $this->assertCount(1, $nurse->reportedDamageReports);
        $this->assertCount(1, $roomLocation->currentAssets);
        $this->assertCount(1, $roomLocation->incomingMovements);
        $this->assertCount(1, $parentLocation->outgoingMovements);
        $this->assertCount(1, $roomLocation->trackingEvents);
        $this->assertCount(1, $roomLocation->alerts);
        $this->assertCount(1, $roomLocation->damageReports);
        $this->assertCount(1, $roomLocation->geofences);
    }

    public function test_scopes_support_search_filters_and_room_based_queries(): void
    {
        $categoryA = $this->createCategory('CAT-DIAG', 'Diagnostics');
        $categoryB = $this->createCategory('CAT-BED', 'Beds');
        $ward = $this->createLocation('WARD-2A', 'Ward 2A', 'room', null, 2, true);
        $storage = $this->createLocation('STORE-1', 'Storage 1', 'storage', null, 1, false);
        $map = $this->createMap($ward);

        $matchingAsset = Asset::create([
            'asset_code' => 'AST-FIND-1',
            'name' => 'Portable Ultrasound',
            'category_id' => $categoryA->id,
            'status' => AssetStatus::Available->value,
            'current_location_id' => $ward->id,
            'current_map_id' => $map->id,
            'barcode_value' => 'BAR-100',
        ]);

        Asset::create([
            'asset_code' => 'AST-FIND-2',
            'name' => 'Patient Bed',
            'category_id' => $categoryB->id,
            'status' => AssetStatus::Maintenance->value,
            'current_location_id' => $storage->id,
        ]);

        $this->assertSame([$categoryA->id], AssetCategory::query()->search('DIAG')->pluck('id')->all());
        $this->assertSame([$ward->id], Location::query()->active()->ofType('room')->onFloor(2)->pluck('id')->all());

        $searchByCategory = Asset::query()->search('Diagnostics')->pluck('id')->all();
        $searchByName = Asset::query()->search('Ultrasound')->pluck('id')->all();
        $searchByBarcode = Asset::query()->search('BAR-100')->pluck('id')->all();

        $this->assertSame([$matchingAsset->id], $searchByCategory);
        $this->assertSame([$matchingAsset->id], $searchByName);
        $this->assertSame([$matchingAsset->id], $searchByBarcode);
        $this->assertSame([$matchingAsset->id], Asset::query()->forCategory($categoryA)->pluck('id')->all());
        $this->assertSame([$matchingAsset->id], Asset::query()->atLocation($ward)->pluck('id')->all());
        $this->assertSame([$matchingAsset->id], Asset::query()->withStatus(AssetStatus::Available)->pluck('id')->all());
    }

    public function test_recency_and_status_scopes_cover_tracking_alert_and_damage_flows(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $category = $this->createCategory();
        $location = $this->createLocation();
        $asset = Asset::create([
            'asset_code' => 'AST-SCOPE-1',
            'name' => 'Ventilator',
            'category_id' => $category->id,
            'status' => AssetStatus::InUse->value,
            'current_location_id' => $location->id,
        ]);

        $olderMovement = AssetMovement::create([
            'asset_id' => $asset->id,
            'to_location_id' => $location->id,
            'movement_source' => MovementSource::Manual->value,
            'moved_at' => '2026-05-04 07:00:00',
        ]);

        $newerMovement = AssetMovement::create([
            'asset_id' => $asset->id,
            'to_location_id' => $location->id,
            'movement_source' => MovementSource::Rfid->value,
            'moved_at' => '2026-05-04 09:00:00',
        ]);

        $olderEvent = AssetTrackingEvent::create([
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'source' => TrackingSource::Manual->value,
            'event_type' => TrackingEventType::Moved->value,
            'detected_at' => '2026-05-04 09:30:00',
        ]);

        $newerEvent = AssetTrackingEvent::create([
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'source' => TrackingSource::Rfid->value,
            'event_type' => TrackingEventType::Exited->value,
            'payload' => ['reader' => 'east-gate'],
            'detected_at' => '2026-05-04 10:00:00',
        ]);

        $openAlert = AssetAlert::create([
            'asset_id' => $asset->id,
            'location_id' => $location->id,
            'tracking_event_id' => $newerEvent->id,
            'alert_type' => AlertType::GeofenceBreach->value,
            'message' => 'Alert still open.',
            'status' => AlertStatus::Acknowledged->value,
            'acknowledged_by_user_id' => $manager->id,
            'triggered_at' => '2026-05-04 10:05:00',
        ]);

        $resolvedAlert = AssetAlert::create([
            'asset_id' => $asset->id,
            'alert_type' => AlertType::GeofenceBreach->value,
            'message' => 'Alert resolved.',
            'status' => AlertStatus::Resolved->value,
            'resolved_at' => '2026-05-04 11:00:00',
            'triggered_at' => '2026-05-04 10:01:00',
        ]);

        $openReport = DamageReport::create([
            'asset_id' => $asset->id,
            'title' => 'Battery warning',
            'description' => 'Battery health warning is shown.',
            'severity' => DamageSeverity::High->value,
            'status' => DamageStatus::InProgress->value,
            'reported_at' => '2026-05-04 11:30:00',
        ]);

        $resolvedReport = DamageReport::create([
            'asset_id' => $asset->id,
            'title' => 'Broken wheel',
            'description' => 'Wheel was replaced.',
            'severity' => DamageSeverity::Low->value,
            'status' => DamageStatus::Resolved->value,
            'resolved_at' => '2026-05-04 12:30:00',
            'reported_at' => '2026-05-04 12:00:00',
        ]);

        $olderRepairUpdate = RepairUpdate::create([
            'damage_report_id' => $openReport->id,
            'updated_by_user_id' => $manager->id,
            'update_type' => RepairUpdateType::Note->value,
            'logged_at' => '2026-05-04 12:35:00',
        ]);

        $newerRepairUpdate = RepairUpdate::create([
            'damage_report_id' => $openReport->id,
            'updated_by_user_id' => $manager->id,
            'update_type' => RepairUpdateType::Repair->value,
            'status_after' => DamageStatus::Resolved->value,
            'logged_at' => '2026-05-04 13:00:00',
        ]);

        $this->assertSame(
            [$newerMovement->id, $olderMovement->id],
            AssetMovement::query()->newestFirst()->pluck('id')->all(),
        );

        $this->assertSame(
            [$newerEvent->id, $olderEvent->id],
            AssetTrackingEvent::query()->recentFirst()->pluck('id')->all(),
        );
        $this->assertSame([$newerEvent->id], AssetTrackingEvent::query()->forAsset($asset)->fromSource(TrackingSource::Rfid)->pluck('id')->all());
        $this->assertSame([$olderEvent->id, $newerEvent->id], AssetTrackingEvent::query()->atLocation($location)->pluck('id')->all());

        $this->assertSame([$openAlert->id], AssetAlert::query()->open()->pluck('id')->all());
        $this->assertSame([$openAlert->id], AssetAlert::query()->acknowledged()->pluck('id')->all());
        $this->assertSame([$resolvedAlert->id], AssetAlert::query()->resolved()->pluck('id')->all());
        $this->assertSame(
            [$openAlert->id, $resolvedAlert->id],
            AssetAlert::query()->recentFirst()->pluck('id')->all(),
        );

        $this->assertSame([$openReport->id], DamageReport::query()->open()->pluck('id')->all());
        $this->assertSame([$resolvedReport->id], DamageReport::query()->resolved()->pluck('id')->all());
        $this->assertSame([$openReport->id], DamageReport::query()->withSeverity(DamageSeverity::High)->pluck('id')->all());
        $this->assertSame([$openReport->id, $resolvedReport->id], DamageReport::query()->forAsset($asset)->pluck('id')->all());

        $this->assertSame(
            [$newerRepairUpdate->id, $olderRepairUpdate->id],
            RepairUpdate::query()->recentFirst()->pluck('id')->all(),
        );
    }

    private function createCategory(string $code = 'CAT-001', string $name = 'Imaging'): AssetCategory
    {
        return AssetCategory::create([
            'code' => $code,
            'name' => $name,
        ]);
    }

    private function createLocation(
        string $code = 'LOC-001',
        string $name = 'Ward A',
        string $type = 'room',
        ?Location $parent = null,
        ?int $floorNumber = 1,
        bool $isActive = true,
    ): Location {
        return Location::create([
            'parent_id' => $parent?->id,
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'floor_number' => $floorNumber,
            'is_active' => $isActive,
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
