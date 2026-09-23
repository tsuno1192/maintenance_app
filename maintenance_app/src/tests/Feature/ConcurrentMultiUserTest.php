<?php

namespace Tests\Feature;

use App\Enums\TroubleStatus;
use App\Enums\UserRole;
use App\Models\Trouble;
use App\Models\User;
use App\Services\TroubleRegistrationService;
use App\Services\TroubleWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class ConcurrentMultiUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_users_can_register_troubles_without_collision(): void
    {
        $userA = User::factory()->role(UserRole::Discoverer, '運転Gr')->create();
        $userB = User::factory()->role(UserRole::Discoverer, '電気Gr')->create();

        $payload = fn (string $group, string $title) => [
            'category_major' => '設備',
            'category_middle' => 'エリア1',
            'category_minor' => '計器',
            'title' => $title,
            'content' => '同時登録テスト',
            'created_group' => $group,
            'reporter_name' => 'tester',
            'occurred_on' => now()->toDateString(),
        ];

        $this->actingAs($userA)
            ->post(route('troubles.store'), $payload('運転Gr', 'トラブルA'))
            ->assertRedirect();

        $this->actingAs($userB)
            ->post(route('troubles.store'), $payload('電気Gr', 'トラブルB'))
            ->assertRedirect();

        $this->assertDatabaseCount('troubles', 2);
        $this->assertDatabaseHas('troubles', ['title' => 'トラブルA', 'created_group' => '運転Gr']);
        $this->assertDatabaseHas('troubles', ['title' => 'トラブルB', 'created_group' => '電気Gr']);
        $this->assertSame(2, Trouble::query()->where('status', TroubleStatus::Leader)->count());
    }

    public function test_concurrent_approve_on_same_trouble_is_serialized(): void
    {
        $trouble = Trouble::factory()
            ->forGroup('運転Gr')
            ->status(TroubleStatus::Leader)
            ->create();

        $leaderA = User::factory()->role(UserRole::Leader, '運転Gr')->create();
        $leaderB = User::factory()->role(UserRole::Leader, '運転Gr')->create();
        $service = app(TroubleWorkflowService::class);

        // ユーザーAが先に承認
        $this->actingAs($leaderA);
        $service->advance($trouble->fresh());
        $this->assertSame(TroubleStatus::OpsManager, $trouble->fresh()->status);

        // ユーザーBが古い状態で承認しようとすると拒否
        $stale = Trouble::query()->findOrFail($trouble->id);
        $stale->status = TroubleStatus::Leader;

        try {
            $this->actingAs($leaderB);
            $service->advance($stale);
            $this->fail('同時承認が許可されてしまいました。');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('他のユーザー', $e->getMessage());
        }

        $this->assertSame(TroubleStatus::OpsManager, $trouble->fresh()->status);
        $this->assertTrue($trouble->fresh()->leader_approved);
    }

    public function test_http_double_approve_does_not_double_advance(): void
    {
        $trouble = Trouble::factory()
            ->forGroup('運転Gr')
            ->status(TroubleStatus::Leader)
            ->create();

        $leader = User::factory()->role(UserRole::Leader, '運転Gr')->create();

        $this->actingAs($leader)
            ->post(route('troubles.approve', $trouble))
            ->assertRedirect();

        // 2回目はロール不一致で 403（現在は ops_manager 待ち）
        $this->actingAs($leader)
            ->post(route('troubles.approve', $trouble))
            ->assertForbidden();

        $this->assertSame(TroubleStatus::OpsManager, $trouble->fresh()->status);
    }

    public function test_transactional_registration_creates_consistent_todos(): void
    {
        $user = User::factory()->role(UserRole::Discoverer, '運転Gr')->create();
        $service = app(TroubleRegistrationService::class);

        DB::transaction(function () use ($service, $user) {
            $service->register([
                'category_major' => '設備',
                'title' => 'TX整合テスト',
                'created_group' => '運転Gr',
                'reporter_name' => $user->name,
                'reporter_user_id' => $user->id,
                'occurred_on' => now()->toDateString(),
            ]);
        });

        $trouble = Trouble::query()->where('title', 'TX整合テスト')->firstOrFail();
        $this->assertSame(2, $trouble->todos()->count());
        $this->assertDatabaseHas('todos', [
            'trouble_id' => $trouble->id,
            'group_name' => '運転Gr',
        ]);
        $this->assertDatabaseHas('todos', [
            'trouble_id' => $trouble->id,
            'group_name' => '保全Gr',
        ]);
    }
}
