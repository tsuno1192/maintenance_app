<?php

namespace Tests\Feature;

use App\Enums\TroubleStatus;
use App\Enums\UserRole;
use App\Models\Trouble;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function protectedGetEndpoints(): array
    {
        return [
            'troubles index' => ['/troubles'],
            'troubles create' => ['/troubles/create'],
            'todos' => ['/todos'],
            'analytics' => ['/analytics'],
            'dashboard' => ['/dashboard'],
            'machines' => ['/machines'],
            'memos' => ['/memos'],
            'tools' => ['/tools'],
        ];
    }

    #[DataProvider('protectedGetEndpoints')]
    public function test_guest_is_redirected_from_protected_get_endpoints(string $uri): void
    {
        $this->get($uri)->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store_trouble(): void
    {
        $this->post('/troubles', [
            'category_major' => '設備',
            'title' => '未認証登録',
            'created_group' => '運転Gr',
            'reporter_name' => 'guest',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('troubles', 0);
    }

    public function test_guest_cannot_approve_or_download_pdf(): void
    {
        $trouble = Trouble::factory()->status(TroubleStatus::Leader)->create();

        $this->post(route('troubles.approve', $trouble))->assertRedirect(route('login'));
        $this->get(route('troubles.pdf', $trouble))->assertRedirect(route('login'));
        $this->assertSame(TroubleStatus::Leader, $trouble->fresh()->status);
    }

    public function test_user_from_other_group_cannot_approve_leader_step(): void
    {
        $trouble = Trouble::factory()
            ->forGroup('運転Gr')
            ->status(TroubleStatus::Leader)
            ->create();

        $otherGroupLeader = User::factory()
            ->role(UserRole::Leader, '電気Gr')
            ->create();

        $this->actingAs($otherGroupLeader)
            ->post(route('troubles.approve', $trouble))
            ->assertForbidden();

        $this->assertSame(TroubleStatus::Leader, $trouble->fresh()->status);
    }

    public function test_ops_leader_cannot_approve_maintenance_step(): void
    {
        $trouble = Trouble::factory()
            ->forGroup('運転Gr')
            ->status(TroubleStatus::MaintenanceLeader)
            ->create();

        $opsLeader = User::factory()
            ->role(UserRole::Leader, '運転Gr')
            ->create();

        $this->actingAs($opsLeader)
            ->post(route('troubles.approve', $trouble))
            ->assertForbidden();

        $this->assertSame(TroubleStatus::MaintenanceLeader, $trouble->fresh()->status);
    }

    public function test_same_group_leader_can_approve(): void
    {
        $trouble = Trouble::factory()
            ->forGroup('運転Gr')
            ->status(TroubleStatus::Leader)
            ->create();

        $leader = User::factory()
            ->role(UserRole::Leader, '運転Gr')
            ->create();

        $this->actingAs($leader)
            ->post(route('troubles.approve', $trouble))
            ->assertRedirect(route('troubles.show', $trouble));

        $this->assertSame(TroubleStatus::OpsManager, $trouble->fresh()->status);
    }
}
