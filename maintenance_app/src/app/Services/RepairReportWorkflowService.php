<?php

namespace App\Services;

use App\Enums\RepairReportStatus;
use App\Models\RepairReport;
use App\Models\Todo;
use App\Models\Trouble;
use App\Notifications\WorkflowStepAdvancedNotification;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RepairReportWorkflowService
{
    public function __construct(
        private readonly WorkflowNotificationService $notifier,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(Trouble $trouble, array $data): RepairReport
    {
        $report = DB::transaction(function () use ($trouble, $data) {
            /** @var Trouble $locked */
            $locked = Trouble::query()
                ->whereKey($trouble->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->repairReport()->exists()) {
                throw new InvalidArgumentException('このトラブルには既に完了報告があります。');
            }

            $report = RepairReport::create([
                ...$data,
                'trouble_id' => $locked->id,
                'status' => RepairReportStatus::Leader,
                'maintenance_staff_approved' => true,
                'maintenance_staff_approved_at' => now(),
            ]);

            Todo::create([
                'group_name' => '保全Gr',
                'title' => "【完了報告承認】{$report->title}",
                'due_on' => $report->repaired_on,
                'is_completed' => false,
                'trouble_id' => $locked->id,
                'user_id' => null,
            ]);

            return $report->load('trouble');
        });

        $this->notifier->notifyNextStep(
            workflow: 'repair_report',
            recipientKey: RepairReportStatus::Leader->recipientKey(),
            notification: WorkflowStepAdvancedNotification::forRepairReport(
                $report,
                RepairReportStatus::MaintenanceStaff->label(),
                RepairReportStatus::Leader->label(),
            ),
        );

        return $report;
    }

    public function advance(RepairReport $report): RepairReport
    {
        $expected = $report->status;

        if ($expected->next() === null) {
            throw new InvalidArgumentException('この完了報告は既に完了しています。');
        }

        $updated = DB::transaction(function () use ($report, $expected) {
            /** @var RepairReport $locked */
            $locked = RepairReport::query()
                ->whereKey($report->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== $expected) {
                throw new InvalidArgumentException('他のユーザーにより状態が更新されたため、承認できません。');
            }

            $current = $locked->status;
            $next = $current->next();

            if ($next === null) {
                throw new InvalidArgumentException('この完了報告は既に完了しています。');
            }

            $flag = $current->approvalFlagColumn();
            $at = $current->approvalAtColumn();

            if ($flag !== null) {
                $locked->{$flag} = true;
            }
            if ($at !== null) {
                $locked->{$at} = now();
            }

            $locked->status = $next;
            $locked->save();

            $this->syncTodos($locked, $next);

            return $locked->fresh('trouble');
        });

        $this->notifier->notifyNextStep(
            workflow: 'repair_report',
            recipientKey: $updated->status->recipientKey(),
            notification: WorkflowStepAdvancedNotification::forRepairReport(
                $updated,
                $expected->label(),
                $updated->status->label(),
            ),
        );

        return $updated;
    }

    protected function syncTodos(RepairReport $report, RepairReportStatus $next): void
    {
        Todo::query()
            ->where('trouble_id', $report->trouble_id)
            ->where('is_completed', false)
            ->where('title', 'like', '【完了報告%')
            ->update(['is_completed' => true]);

        if ($next === RepairReportStatus::Completed) {
            return;
        }

        $group = match ($next) {
            RepairReportStatus::Leader, RepairReportStatus::Manager => '保全Gr',
            RepairReportStatus::OpsLeader, RepairReportStatus::OpsManager => $report->trouble?->created_group ?: '運転Gr',
            default => '保全Gr',
        };

        Todo::create([
            'group_name' => $group,
            'title' => "【完了報告承認:{$next->label()}】{$report->title}",
            'due_on' => $report->repaired_on,
            'is_completed' => false,
            'trouble_id' => $report->trouble_id,
            'user_id' => null,
        ]);
    }
}
