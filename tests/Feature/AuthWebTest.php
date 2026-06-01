<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\LocationMap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads_for_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Silahkan login untuk melanjutkan');
    }

    public function test_user_can_log_in_with_email(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Staff,
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'login_identity' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_log_in_with_name_as_username(): void
    {
        $user = User::factory()->create([
            'name' => 'Nurse User',
            'role' => UserRole::Nurse,
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'login_identity' => 'Nurse User',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_guest_is_redirected_to_login_from_protected_page(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_forbidden_role_receives_403_page(): void
    {
        $nurse = User::factory()->create([
            'role' => UserRole::Nurse,
        ]);

        $asset = $this->createAsset();

        $this->actingAs($nurse)
            ->get(route('web.assets.tracking.show', $asset))
            ->assertForbidden()
            ->assertSee('403 Forbidden');
    }

    public function test_manager_can_access_manager_only_location_index(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::Manager,
        ]);

        $this->actingAs($manager)
            ->get(route('web.locations.index'))
            ->assertOk();
    }

    public function test_manager_can_access_staff_only_tracking_page_via_web_wide_bypass(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::Manager,
        ]);

        $asset = $this->createAsset();

        $this->actingAs($manager)
            ->get(route('web.assets.tracking.show', $asset))
            ->assertOk();
    }

    public function test_administrator_can_access_all_protected_pages(): void
    {
        $administrator = User::factory()->create([
            'role' => UserRole::Administrator,
        ]);

        $asset = $this->createAsset();

        $this->actingAs($administrator)
            ->get(route('web.assets.tracking.show', $asset))
            ->assertOk();

        $this->actingAs($administrator)
            ->get(route('web.locations.index'))
            ->assertOk();

        $this->actingAs($administrator)
            ->get(route('web.location-assets.index'))
            ->assertOk();

        $this->actingAs($administrator)
            ->get(route('web.qr-labels.redirect', 'ABCDEFGHIJ'))
            ->assertNotFound();
    }

    protected function createAsset(): Asset
    {
        $category = AssetCategory::query()->create([
            'code' => 'CAT-AUTH',
            'name' => 'Kategori Auth',
        ]);

        $locationMap = LocationMap::query()->create([
            'name' => 'Gedung Auth',
            'image_path' => 'ignored.svg',
            'notes' => 'Auth test',
        ]);

        $location = Location::query()->create([
            'code' => 'LOC-AUTH',
            'name' => 'Ruangan Auth',
            'type' => 'Lab',
            'location_map_id' => $locationMap->id,
            'is_active' => true,
        ]);

        return Asset::query()->create([
            'asset_code' => 'AST-AUTH',
            'name' => 'Aset Auth',
            'category_id' => $category->id,
            'current_location_id' => $location->id,
            'status' => 'available',
        ]);
    }
}
