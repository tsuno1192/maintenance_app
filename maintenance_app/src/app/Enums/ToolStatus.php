<?php

namespace App\Enums;

enum ToolStatus: string
{
    case Available = 'available';
    case InUse = 'in_use';
    case Maintenance = 'maintenance';
    case Lost = 'lost';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Available => '利用可',
            self::InUse => '貸出中',
            self::Maintenance => '点検中',
            self::Lost => '紛失',
            self::Retired => '廃棄',
        };
    }
}
