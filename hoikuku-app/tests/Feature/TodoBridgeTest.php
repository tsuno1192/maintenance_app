<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TodoBridgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_todos(): void
    {
        $this->get(route('todos.index'))->assertRedirect(route('login'));
    }

    public function test_parent_cannot_access_todos(): void
    {
        $parent = User::factory()->parent()->create();

        $this->actingAs($parent)
            ->get(route('todos.index'))
            ->assertForbidden();
    }

    public function test_staff_can_list_and_create_remote_tasks(): void
    {
        config([
            'services.todo.base_url' => 'http://todo.test',
            'services.todo.token' => 'bridge-secret-token-0123456789abcdef',
        ]);

        Http::fake([
            'todo.test/api/tasks*' => Http::sequence()
                ->push([
                    'status' => 'success',
                    'data' => [
                        ['id' => 1, 'title' => '既存タスク', 'status' => 'pending'],
                    ],
                    'meta' => ['total' => 1],
                ])
                ->push([
                    'status' => 'success',
                    'message' => 'Task created.',
                    'data' => ['id' => 2, 'title' => '新規タスク', 'status' => 'pending'],
                ], 201)
                ->push([
                    'status' => 'success',
                    'data' => [
                        ['id' => 1, 'title' => '既存タスク', 'status' => 'pending'],
                        ['id' => 2, 'title' => '新規タスク', 'status' => 'pending'],
                    ],
                    'meta' => ['total' => 2],
                ]),
        ]);

        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('todos.index'))
            ->assertOk()
            ->assertSee('既存タスク');

        $this->actingAs($staff)
            ->post(route('todos.store'), [
                'title' => '新規タスク',
                'description' => '説明',
            ])
            ->assertRedirect(route('todos.index'));

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer bridge-secret-token-0123456789abcdef')
                && $request->url() === 'http://todo.test/api/tasks'
                && $request['title'] === '新規タスク';
        });
    }

    public function test_voice_command_creates_task_via_api(): void
    {
        config([
            'services.todo.base_url' => 'http://todo.test',
            'services.todo.token' => 'bridge-secret-token-0123456789abcdef',
        ]);

        Http::fake([
            'todo.test/api/tasks' => Http::response([
                'status' => 'success',
                'data' => ['id' => 10, 'title' => '牛乳を買う', 'status' => 'pending'],
            ], 201),
        ]);

        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->postJson(route('todos.voice'), [
                'transcript' => '牛乳を買うを追加',
                'mode' => 'command',
            ])
            ->assertCreated()
            ->assertJsonPath('action', 'create');
    }
}
