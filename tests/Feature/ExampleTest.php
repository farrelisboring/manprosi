<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->withoutVite();
        $this->signInAsRole(UserRole::Staff);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Hospital assets at a glance')
            ->assertSee('Add asset');
    }
}
