<?php

namespace App\Policies;

use App\Enums\TroubleStatus;
use App\Enums\UserRole;
use App\Models\Trouble;
use App\Models\User;

class TroublePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Trouble $trouble): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function downloadPdf(User $user, Trouble $trouble): bool
    {
        return true;
    }

    /**
     * 現在ステータスに対応するロール、かつ同一グループ（または保全側ロール）のみ承認可。
     */
    public function approve(User $user, Trouble $trouble): bool
    {
        $role = $user->role instanceof UserRole ? $user->role : UserRole::tryFrom((string) $user->role);

        if ($role === null) {
            return false;
        }

        if (! $trouble->status->canAdvance()) {
            return false;
        }

        if (! $role->canApproveTrouble($trouble->status)) {
            return false;
        }

        if ($role === UserRole::Admin) {
            return true;
        }

        return $this->belongsToResponsibleGroup($user, $trouble, $role);
    }

    protected function belongsToResponsibleGroup(User $user, Trouble $trouble, UserRole $role): bool
    {
        $userGroup = (string) $user->group_name;
        $createdGroup = (string) $trouble->created_group;

        return match ($trouble->status) {
            TroubleStatus::Discoverer,
            TroubleStatus::Leader,
            TroubleStatus::OpsManager => $userGroup !== '' && $userGroup === $createdGroup,
            TroubleStatus::MaintenanceLeader,
            TroubleStatus::MaintenanceManager => $userGroup === '保全Gr' || $role->isMaintenanceSide(),
            default => false,
        };
    }
}
