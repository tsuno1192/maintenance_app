<?php

namespace Tests\Feature\Auth;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_parent_can_register_with_valid_child_id(): void
    {
        $child = Child::create([
            'name' => 'はな',
            'birth_date' => '2021-01-01',
        ]);

        $response = $this->post('/register', [
            'name' => '保護者太郎',
            'child_id' => $child->id,
            'email' => 'parent@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = User::where('email', 'parent@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('parent', $user->role);
        $this->assertSame($child->id, $user->child_id);
    }

    public function test_registration_rejects_unknown_child_id(): void
    {
        $this->post('/register', [
            'name' => '保護者',
            'child_id' => 9999,
            'email' => 'parent2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('child_id');

        $this->assertGuest();
    }
}
