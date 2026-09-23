<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_api_token(): void
    {
        $this->getJson('/api/tasks')->assertUnauthorized();
    }

    public function test_rejects_x_api_key_header(): void
    {
        config(['services.api_token' => 'test-token-0123456789abcdef']);

        $this->withHeaders(['X-API-Key' => 'test-token-0123456789abcdef'])
            ->getJson('/api/tasks')
            ->assertUnauthorized();
    }

    public function test_can_list_create_update_and_show_tasks_with_bearer_token(): void
    {
        config(['services.api_token' => 'test-token-0123456789abcdef']);

        $this->withToken('test-token-0123456789abcdef')
            ->postJson('/api/tasks', [
                'title' => '保護者面談準備',
                'description' => '資料を印刷する',
                'source' => 'hoikuku-app',
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', '保護者面談準備');

        $task = Task::first();
        $this->assertNotNull($task);

        $this->withToken('test-token-0123456789abcdef')
            ->getJson('/api/tasks/'.$task->id)
            ->assertOk()
            ->assertJsonPath('data.id', $task->id);

        $this->withToken('test-token-0123456789abcdef')
            ->patchJson('/api/tasks/'.$task->id, ['status' => 'done'])
            ->assertOk()
            ->assertJsonPath('data.status', 'done');

        $this->withToken('test-token-0123456789abcdef')
            ->getJson('/api/tasks?status=done&source=hoikuku-app')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_returns_service_unavailable_when_token_missing(): void
    {
        config(['services.api_token' => '']);

        $this->withToken('anything')
            ->getJson('/api/tasks')
            ->assertStatus(503);
    }
}
