<?php

namespace Tests\Feature;

use App\Enums\RepairReportStatus;
use App\Enums\TroubleStatus;
use App\Enums\UserRole;
use App\Models\RepairReport;
use App\Models\Trouble;
use App\Models\User;
use App\Services\RepairReportWorkflowService;
use App\Services\TroubleWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class WorkflowTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_trouble_workflow_advances_only_in_order(): void
    {
        $trouble = Trouble::factory()->status(TroubleStatus::Leader)->create();
        $service = app(TroubleWorkflowService::class);

        $expected = [
            TroubleStatus::OpsManager,
            TroubleStatus::MaintenanceLeader,
            TroubleStatus::MaintenanceManager,
            TroubleStatus::Completed,
        ];

        foreach ($expected as $status) {
            $trouble = $service->advance($trouble->fresh());
            $this->assertSame($status, $trouble->status);
        }

        $this->expectException(InvalidArgumentException::class);
        $service->advance($trouble->fresh());
    }

    public function test_stale_trouble_status_cannot_skip_ahead(): void
    {
        $trouble = Trouble::factory()->status(TroubleStatus::Leader)->create();
        $service = app(TroubleWorkflowService::class);

        $service->advance($trouble->fresh());
        $this->assertSame(TroubleStatus::OpsManager, $trouble->fresh()->status);

        // 古い Leader 状態のまま再送
        $stale = $trouble->fresh();
        $stale->status = TroubleStatus::Leader;

        $this->expectException(InvalidArgumentException::class);
        $service->advance($stale);
    }

    public function test_http_approve_follows_role_order(): void
    {
        $trouble = Trouble::factory()
            ->forGroup('運転Gr')
            ->status(TroubleStatus::Leader)
            ->create();

        $leader = User::factory()->role(UserRole::Leader, '運転Gr')->create();
        $opsManager = User::factory()->role(UserRole::OpsManager, '運転Gr')->create();
        $maintLeader = User::factory()->role(UserRole::MaintenanceLeader, '保全Gr')->create();
        $maintManager = User::factory()->role(UserRole::MaintenanceManager, '保全Gr')->create();

        $this->actingAs($leader)->post(route('troubles.approve', $trouble))->assertRedirect();
        $this->assertSame(TroubleStatus::OpsManager, $trouble->fresh()->status);

        $this->actingAs($opsManager)->post(route('troubles.approve', $trouble))->assertRedirect();
        $this->assertSame(TroubleStatus::MaintenanceLeader, $trouble->fresh()->status);

        $this->actingAs($maintLeader)->post(route('troubles.approve', $trouble))->assertRedirect();
        $this->assertSame(TroubleStatus::MaintenanceManager, $trouble->fresh()->status);

        $this->actingAs($maintManager)->post(route('troubles.approve', $trouble))->assertRedirect();
        $this->assertSame(TroubleStatus::Completed, $trouble->fresh()->status);
    }

    public function test_repair_report_workflow_advances_only_in_order(): void
    {
        $trouble = Trouble::factory()->status(TroubleStatus::Completed)->create([
            'created_group' => '運転Gr',
        ]);
        $report = RepairReport::factory()
            ->for($trouble)
            ->status(RepairReportStatus::Leader)
            ->create();

        $service = app(RepairReportWorkflowService::class);

        $expected = [
            RepairReportStatus::Manager,
            RepairReportStatus::OpsLeader,
            RepairReportStatus::OpsManager,
            RepairReportStatus::Completed,
        ];

        foreach ($expected as $status) {
            $report = $service->advance($report->fresh());
            $this->assertSame($status, $report->status);
        }

        $this->expectException(InvalidArgumentException::class);
        $service->advance($report->fresh());
    }

    public function test_repair_report_http_workflow_with_roles(): void
    {
        $trouble = Trouble::factory()->status(TroubleStatus::Completed)->create([
            'created_group' => '運転Gr',
        ]);
        $report = RepairReport::factory()
            ->for($trouble)
            ->status(RepairReportStatus::Leader)
            ->create();

        $maintLeader = User::factory()->role(UserRole::MaintenanceLeader, '保全Gr')->create();
        $maintManager = User::factory()->role(UserRole::MaintenanceManager, '保全Gr')->create();
        $opsLeader = User::factory()->role(UserRole::OpsLeader, '運転Gr')->create();
        $opsManager = User::factory()->role(UserRole::OpsManager, '運転Gr')->create();

        $this->actingAs($maintLeader)->post(route('repair-reports.approve', $report))->assertRedirect();
        $this->assertSame(RepairReportStatus::Manager, $report->fresh()->status);

        $this->actingAs($maintManager)->post(route('repair-reports.approve', $report))->assertRedirect();
        $this->assertSame(RepairReportStatus::OpsLeader, $report->fresh()->status);

        $this->actingAs($opsLeader)->post(route('repair-reports.approve', $report))->assertRedirect();
        $this->assertSame(RepairReportStatus::OpsManager, $report->fresh()->status);

        $this->actingAs($opsManager)->post(route('repair-reports.approve', $report))->assertRedirect();
        $this->assertSame(RepairReportStatus::Completed, $report->fresh()->status);
    }
}
