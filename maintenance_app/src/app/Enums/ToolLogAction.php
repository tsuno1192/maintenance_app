<?php

namespace App\Enums;

enum ToolLogAction: string
{
    case Checkout = 'checkout';
    case Checkin = 'checkin';
    case Inspect = 'inspect';
    case Adjust = 'adjust';
    case Lost = 'lost';
    case Retire = 'retire';

    public function label(): string
    {
        return match ($this) {
            self::Checkout => '貸出',
            self::Checkin => '返却',
            self::Inspect => '点検',
            self::Adjust => '在庫調整',
            self::Lost => '紛失',
            self::Retire => '廃棄',
        };
    }

    public function resultingStatus(): ?ToolStatus
    {
        return match ($this) {
            self::Checkout => ToolStatus::InUse,
            self::Checkin => ToolStatus::Available,
            self::Inspect => ToolStatus::Maintenance,
            self::Lost => ToolStatus::Lost,
            self::Retire => ToolStatus::Retired,
            self::Adjust => null,
        };
    }
}
