<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk()->assertSee('ログイン');
    }

    public function test_users_can_authenticate_and_reach_dashboard(): void
    {
        $user = User::factory()->role(UserRole::Discoverer, '運転Gr')->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->get('/dashboard')->assertOk();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_register(): void
    {
        $this->post('/register', [
            'name' => '新規ユーザー',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'group_name' => '運転Gr',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'group_name' => '運転Gr',
        ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
