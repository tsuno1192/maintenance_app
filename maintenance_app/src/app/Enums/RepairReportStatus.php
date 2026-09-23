<?php

namespace App\Enums;

enum RepairReportStatus: string
{
    /** 保全Gr対応者 */
    case MaintenanceStaff = 'maintenance_staff';

    /** リーダ */
    case Leader = 'leader';

    /** 管理者上長（保全側） */
    case Manager = 'manager';

    /** 運転Grリーダ */
    case OpsLeader = 'ops_leader';

    /** 管理者上長（運転側） */
    case OpsManager = 'ops_manager';

    /** 完了 */
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::MaintenanceStaff => '保全Gr対応者',
            self::Leader => 'リーダ',
            self::Manager => '管理者上長',
            self::OpsLeader => '運転Grリーダ',
            self::OpsManager => '管理者上長（運転）',
            self::Completed => '完了',
        };
    }

    public function approvalFlagColumn(): ?string
    {
        return match ($this) {
            self::MaintenanceStaff => 'maintenance_staff_approved',
            self::Leader => 'leader_approved',
            self::Manager => 'manager_approved',
            self::OpsLeader => 'ops_leader_approved',
            self::OpsManager => 'ops_manager_approved',
            self::Completed => null,
        };
    }

    public function approvalAtColumn(): ?string
    {
        return match ($this) {
            self::MaintenanceStaff => 'maintenance_staff_approved_at',
            self::Leader => 'leader_approved_at',
            self::Manager => 'manager_approved_at',
            self::OpsLeader => 'ops_leader_approved_at',
            self::OpsManager => 'ops_manager_approved_at',
            self::Completed => null,
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::MaintenanceStaff => self::Leader,
            self::Leader => self::Manager,
            self::Manager => self::OpsLeader,
            self::OpsLeader => self::OpsManager,
            self::OpsManager => self::Completed,
            self::Completed => null,
        };
    }

    public function canAdvance(): bool
    {
        return $this->next() !== null;
    }

    public function recipientKey(): string
    {
        return $this->value;
    }

    /**
     * @return list<self>
     */
    public static function workflowSteps(): array
    {
        return [
            self::MaintenanceStaff,
            self::Leader,
            self::Manager,
            self::OpsLeader,
            self::OpsManager,
            self::Completed,
        ];
    }

    public function approveButtonLabel(): string
    {
        $next = $this->next();

        if ($next === null) {
            return '完了済み';
        }

        if ($next === self::Completed) {
            return '最終承認して完了する';
        }

        return "{$this->label()}として承認し、{$next->label()}へ進める";
    }

    public function stepIndex(): int
    {
        return array_search($this, self::workflowSteps(), true);
    }

    public function isAfter(self $other): bool
    {
        return $this->stepIndex() > $other->stepIndex();
    }
}
