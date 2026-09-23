<?php

namespace Tests\Feature;

use App\Enums\MemoPriority;
use App\Enums\ToolLogAction;
use App\Enums\ToolStatus;
use App\Enums\UserRole;
use App\Models\Machine;
use App\Models\Memo;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldNoteIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->role(UserRole::Discoverer, '運転Gr')->create();
    }

    public function test_authenticated_user_can_open_dashboard_and_field_note_indexes(): void
    {
        $user = $this->user();

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('ダッシュボード');
        $this->actingAs($user)->get('/machines')->assertOk()->assertSee('設備マスタ');
        $this->actingAs($user)->get('/memos')->assertOk()->assertSee('現場申し送り');
        $this->actingAs($user)->get('/tools')->assertOk()->assertSee('工具管理');
    }

    public function test_user_can_register_machine_memo_and_tool_workflow(): void
    {
        $user = $this->user();

        $this->actingAs($user)->post('/machines', [
            'code' => 'EQ-999',
            'name' => 'テストポンプ',
            'area' => '第1プラント',
            'category' => '回転機',
            'status' => 'running',
        ])->assertRedirect();

        $machine = Machine::query()->where('code', 'EQ-999')->firstOrFail();

        $this->actingAs($user)->post('/memos', [
            'title' => 'テスト申し送り',
            'body' => '次直確認',
            'machine_id' => $machine->id,
            'shift' => 'day',
            'priority' => MemoPriority::Urgent->value,
            'category' => '運転',
        ])->assertRedirect();

        $memo = Memo::query()->where('title', 'テスト申し送り')->firstOrFail();
        $this->assertSame($user->id, $memo->user_id);
        $this->assertSame($machine->id, $memo->machine_id);

        $this->actingAs($user)
            ->post(route('memos.acknowledge', $memo))
            ->assertRedirect();

        $this->assertNotNull($memo->fresh()->acknowledged_at);

        $this->actingAs($user)->post('/tools', [
            'code' => 'TL-999',
            'name' => 'テストレンチ',
            'category' => '手工具',
            'location' => '工具室A',
            'status' => ToolStatus::Available->value,
            'quantity' => 2,
        ])->assertRedirect();

        $tool = Tool::query()->where('code', 'TL-999')->firstOrFail();

        $this->actingAs($user)->post(route('tools.logs.store', $tool), [
            'action' => ToolLogAction::Checkout->value,
            'quantity' => 1,
            'note' => '現場持出',
        ])->assertRedirect(route('tools.show', $tool));

        $tool->refresh();
        $this->assertSame(ToolStatus::InUse, $tool->status);
        $this->assertSame(1, $tool->quantity);
        $this->assertSame(1, $tool->logs()->count());
    }

    public function test_navigation_layout_contains_integrated_links(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('現場トラブル')
            ->assertSee('設備マスタ')
            ->assertSee('現場申し送り')
            ->assertSee('工具管理')
            ->assertSee(route('troubles.index'), false)
            ->assertSee(route('todos.index'), false)
            ->assertSee(route('machines.index'), false)
            ->assertSee(route('memos.index'), false)
            ->assertSee(route('tools.index'), false);
    }
}
