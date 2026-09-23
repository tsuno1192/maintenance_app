<?php

namespace App\Services;

use App\Enums\TroubleStatus;
use App\Models\Todo;
use App\Models\Trouble;
use Illuminate\Support\Facades\DB;

class TroubleRegistrationService
{
    public const MAINTENANCE_GROUP = '保全Gr';

    public function __construct(
        private readonly TroubleWorkflowService $workflowService,
    ) {}

    /**
     * トラブルを登録し、関連グループの TO DO を自動作成する。
     *
     * @param  array<string, mixed>  $data
     */
    public function register(array $data): Trouble
    {
        $trouble = DB::transaction(function () use ($data) {
            $trouble = Trouble::create([
                ...$data,
                'status' => TroubleStatus::Leader,
                'discoverer_approved' => true,
                'discoverer_approved_at' => now(),
            ]);

            $this->createLinkedTodos($trouble);

            return $trouble->load('todos');
        });

        $this->workflowService->notifyRegistered($trouble);

        return $trouble;
    }

    /**
     * 登録グループ（承認対応）と保全Gr（補修準備）へ TO DO を作成する。
     */
    protected function createLinkedTodos(Trouble $trouble): void
    {
        $dueOn = $trouble->repair_requested_on ?? $trouble->occurred_on;

        Todo::create([
            'group_name' => $trouble->created_group,
            'title' => "【承認対応】{$trouble->title}",
            'due_on' => $dueOn,
            'is_completed' => false,
            'trouble_id' => $trouble->id,
            'user_id' => $trouble->reporter_user_id,
        ]);

        Todo::create([
            'group_name' => self::MAINTENANCE_GROUP,
            'title' => "【補修準備】{$trouble->title}",
            'due_on' => $dueOn,
            'is_completed' => false,
            'trouble_id' => $trouble->id,
            'user_id' => null,
        ]);
    }
}
