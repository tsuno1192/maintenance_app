<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Discoverer = 'discoverer';
    case Leader = 'leader';
    case OpsManager = 'ops_manager';
    case MaintenanceStaff = 'maintenance_staff';
    case MaintenanceLeader = 'maintenance_leader';
    case MaintenanceManager = 'maintenance_manager';
    case OpsLeader = 'ops_leader';

    public function label(): string
    {
        return match ($this) {
            self::Admin => '管理者',
            self::Discoverer => '発見者',
            self::Leader => 'リーダ',
            self::OpsManager => '管理者上長（運転）',
            self::MaintenanceStaff => '保全Gr対応者',
            self::MaintenanceLeader => '保全Grリーダ',
            self::MaintenanceManager => '管理者上長（保全）',
            self::OpsLeader => '運転Grリーダ',
        };
    }

    public function canApproveTrouble(TroubleStatus $status): bool
    {
        if ($this === self::Admin) {
            return true;
        }

        return match ($status) {
            TroubleStatus::Discoverer => in_array($this, [self::Discoverer, self::Leader], true),
            TroubleStatus::Leader => $this === self::Leader,
            TroubleStatus::OpsManager => $this === self::OpsManager,
            TroubleStatus::MaintenanceLeader => $this === self::MaintenanceLeader,
            TroubleStatus::MaintenanceManager => $this === self::MaintenanceManager,
            TroubleStatus::Completed => false,
        };
    }

    public function canApproveRepairReport(RepairReportStatus $status): bool
    {
        if ($this === self::Admin) {
            return true;
        }

        return match ($status) {
            RepairReportStatus::MaintenanceStaff => $this === self::MaintenanceStaff,
            RepairReportStatus::Leader => in_array($this, [self::Leader, self::MaintenanceLeader], true),
            RepairReportStatus::Manager => $this === self::MaintenanceManager,
            RepairReportStatus::OpsLeader => $this === self::OpsLeader,
            RepairReportStatus::OpsManager => $this === self::OpsManager,
            RepairReportStatus::Completed => false,
        };
    }

    public function isMaintenanceSide(): bool
    {
        return in_array($this, [
            self::MaintenanceStaff,
            self::MaintenanceLeader,
            self::MaintenanceManager,
        ], true);
    }

    public function isOpsSide(): bool
    {
        return in_array($this, [
            self::Discoverer,
            self::Leader,
            self::OpsManager,
            self::OpsLeader,
        ], true);
    }
}
