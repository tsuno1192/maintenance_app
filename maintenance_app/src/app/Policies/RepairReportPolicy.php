<?php

namespace App\Policies;

use App\Enums\RepairReportStatus;
use App\Enums\UserRole;
use App\Models\RepairReport;
use App\Models\User;

class RepairReportPolicy
{
    public function view(User $user, RepairReport $repairReport): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        $role = $user->role instanceof UserRole ? $user->role : UserRole::tryFrom((string) $user->role);

        return $role === UserRole::Admin
            || $role === UserRole::MaintenanceStaff
            || $role === UserRole::MaintenanceLeader
            || $role === UserRole::MaintenanceManager;
    }

    public function approve(User $user, RepairReport $repairReport): bool
    {
        $role = $user->role instanceof UserRole ? $user->role : UserRole::tryFrom((string) $user->role);

        if ($role === null) {
            return false;
        }

        if (! $repairReport->status->canAdvance()) {
            return false;
        }

        if (! $role->canApproveRepairReport($repairReport->status)) {
            return false;
        }

        if ($role === UserRole::Admin) {
            return true;
        }

        $userGroup = (string) $user->group_name;
        $opsGroup = (string) ($repairReport->trouble?->created_group ?: '運転Gr');

        return match ($repairReport->status) {
            RepairReportStatus::MaintenanceStaff,
            RepairReportStatus::Leader,
            RepairReportStatus::Manager => $userGroup === '保全Gr' || $role->isMaintenanceSide(),
            RepairReportStatus::OpsLeader,
            RepairReportStatus::OpsManager => $userGroup !== '' && $userGroup === $opsGroup,
            default => false,
        };
    }
}
