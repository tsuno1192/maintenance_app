<?php

namespace App\Enums;

enum MachineStatus: string
{
    case Running = 'running';
    case Stopped = 'stopped';
    case Maintenance = 'maintenance';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Running => '稼働中',
            self::Stopped => '停止',
            self::Maintenance => '保全中',
            self::Retired => '廃止',
        };
    }
}
