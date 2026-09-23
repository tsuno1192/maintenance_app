<?php

namespace App\Services;

use App\Enums\TroubleStatus;
use App\Models\Todo;
use App\Models\Trouble;
use App\Notifications\WorkflowStepAdvancedNotification;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TroubleWorkflowService
{
    public function __construct(
        private readonly WorkflowNotificationService $notifier,
    ) {}

    /**
     * 現在ステータスを承認し、次ステップへ進める。
     */
    public function advance(Trouble $trouble): Trouble
    {
        $expected = $trouble->status;

        if ($expected->next() === null) {
            throw new InvalidArgumentException('このトラブルは既に完了しています。');
        }

        $updated = DB::transaction(function () use ($trouble, $expected) {
            /** @var Trouble $locked */
            $locked = Trouble::query()
                ->whereKey($trouble->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== $expected) {
                throw new InvalidArgumentException('他のユーザーにより状態が更新されたため、承認できません。');
            }

            $current = $locked->status;
            $next = $current->next();

            if ($next === null) {
                throw new InvalidArgumentException('このトラブルは既に完了しています。');
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

            return $locked->fresh(['todos', 'repairReport']);
        });

        $this->notifier->notifyNextStep(
            workflow: 'trouble',
            recipientKey: $updated->status->recipientKey(),
            notification: WorkflowStepAdvancedNotification::forTrouble(
                $updated,
                $expected->label(),
                $updated->status->label(),
            ),
        );

        return $updated;
    }

    public function notifyRegistered(Trouble $trouble): void
    {
        $this->notifier->notifyNextStep(
            workflow: 'trouble',
            recipientKey: TroubleStatus::Leader->recipientKey(),
            notification: WorkflowStepAdvancedNotification::forTrouble(
                $trouble,
                TroubleStatus::Discoverer->label(),
                TroubleStatus::Leader->label(),
            ),
        );
    }

    protected function syncTodos(Trouble $trouble, TroubleStatus $next): void
    {
        Todo::query()
            ->where('trouble_id', $trouble->id)
            ->where('is_completed', false)
            ->where('title', 'like', '【承認対応%')
            ->update(['is_completed' => true]);

        if ($next === TroubleStatus::Completed) {
            Todo::query()
                ->where('trouble_id', $trouble->id)
                ->where('is_completed', false)
                ->update(['is_completed' => true]);

            return;
        }

        $group = match ($next) {
            TroubleStatus::Leader, TroubleStatus::OpsManager => $trouble->created_group ?: '運転Gr',
            TroubleStatus::MaintenanceLeader, TroubleStatus::MaintenanceManager => '保全Gr',
            default => $trouble->created_group,
        };

        Todo::create([
            'group_name' => $group,
            'title' => "【承認対応:{$next->label()}】{$trouble->title}",
            'due_on' => $trouble->repair_requested_on ?? $trouble->occurred_on,
            'is_completed' => false,
            'trouble_id' => $trouble->id,
            'user_id' => null,
        ]);
    }
}
