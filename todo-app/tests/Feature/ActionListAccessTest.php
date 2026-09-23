<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActionListAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_require_login(): void
    {
        $this->get(route('tasks.index'))->assertRedirect(route('login'));
    }

    public function test_can_login_with_access_token_and_view_tasks(): void
    {
        config(['services.api_token' => 'ui-secret-token-0123456789abcdef']);

        $this->post(route('login.store'), [
            'access_token' => 'ui-secret-token-0123456789abcdef',
        ])->assertRedirect(route('tasks.index'));

        $this->get(route('tasks.index'))->assertOk();
    }
}
