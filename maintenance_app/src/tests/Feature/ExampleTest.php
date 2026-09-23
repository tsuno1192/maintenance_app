<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_guests_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_troubles_index(): void
    {
        $user = User::factory()->role(UserRole::Discoverer, '運転Gr')->create();

        $this->actingAs($user)
            ->get('/troubles')
            ->assertOk();
    }

    public function test_authenticated_user_can_open_dashboard(): void
    {
        $user = User::factory()->role(UserRole::Discoverer, '運転Gr')->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }
}
