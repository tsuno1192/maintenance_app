<?php

namespace App\Enums;

enum TroubleStatus: string
{
    /** 発見者 */
    case Discoverer = 'discoverer';

    /** リーダ */
    case Leader = 'leader';

    /** 管理者上長（運転側） */
    case OpsManager = 'ops_manager';

    /** 保全Grリーダ */
    case MaintenanceLeader = 'maintenance_leader';

    /** 管理者上長（保全側） */
    case MaintenanceManager = 'maintenance_manager';

    /** 完了 */
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Discoverer => '発見者',
            self::Leader => 'リーダ',
            self::OpsManager => '管理者上長',
            self::MaintenanceLeader => '保全Grリーダ',
            self::MaintenanceManager => '管理者上長（保全）',
            self::Completed => '完了',
        };
    }

    /**
     * 現在ステップ承認時に立てるフラグ列。
     */
    public function approvalFlagColumn(): ?string
    {
        return match ($this) {
            self::Discoverer => 'discoverer_approved',
            self::Leader => 'leader_approved',
            self::OpsManager => 'ops_manager_approved',
            self::MaintenanceLeader => 'maintenance_leader_approved',
            self::MaintenanceManager => 'maintenance_manager_approved',
            self::Completed => null,
        };
    }

    public function approvalAtColumn(): ?string
    {
        return match ($this) {
            self::Discoverer => 'discoverer_approved_at',
            self::Leader => 'leader_approved_at',
            self::OpsManager => 'ops_manager_approved_at',
            self::MaintenanceLeader => 'maintenance_leader_approved_at',
            self::MaintenanceManager => 'maintenance_manager_approved_at',
            self::Completed => null,
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Discoverer => self::Leader,
            self::Leader => self::OpsManager,
            self::OpsManager => self::MaintenanceLeader,
            self::MaintenanceLeader => self::MaintenanceManager,
            self::MaintenanceManager => self::Completed,
            self::Completed => null,
        };
    }

    public function canAdvance(): bool
    {
        return $this->next() !== null;
    }

    /**
     * config/tmq.php の通知宛先キー。
     */
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
            self::Discoverer,
            self::Leader,
            self::OpsManager,
            self::MaintenanceLeader,
            self::MaintenanceManager,
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

    public function isAtOrAfter(self $other): bool
    {
        return $this->stepIndex() >= $other->stepIndex();
    }

    public function isAfter(self $other): bool
    {
        return $this->stepIndex() > $other->stepIndex();
    }
}
