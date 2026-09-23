<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoiceTaskTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateUi(): void
    {
        config(['services.api_token' => 'test-ui-token-0123456789abcdef']);
        $this->withSession(['action_list_authenticated' => true]);
    }

    public function test_guest_cannot_use_voice_endpoint(): void
    {
        $this->postJson(route('tasks.voice'), [
            'transcript' => '牛乳を買うを追加',
            'mode' => 'command',
        ])->assertUnauthorized();
    }

    public function test_voice_can_create_task(): void
    {
        $this->authenticateUi();

        $this->postJson(route('tasks.voice'), [
            'transcript' => '牛乳を買うを追加',
            'mode' => 'command',
        ])
            ->assertCreated()
            ->assertJsonPath('action', 'create');

        $this->assertDatabaseHas('tasks', [
            'title' => '牛乳を買う',
            'source' => 'action-list-voice',
        ]);
    }

    public function test_voice_can_complete_task(): void
    {
        $this->authenticateUi();

        $task = Task::create([
            'title' => '会議資料',
            'status' => 'pending',
            'source' => 'action-list',
        ]);

        $this->postJson(route('tasks.voice'), [
            'transcript' => '会議資料を完了して',
            'mode' => 'command',
        ])
            ->assertOk()
            ->assertJsonPath('action', 'complete');

        $this->assertSame('done', $task->fresh()->status);
    }
}
