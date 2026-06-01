<?php

namespace Tests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['view.compiled' => sys_get_temp_dir()]);
    }

    protected function signInAsRole(UserRole $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => $role,
        ], $attributes));

        $this->actingAs($user);

        return $user;
    }
}
