<?php

namespace App\Enums;

/**
 * 設備（マシン）の稼働ステータス。
 */
enum MachineStatus: string
{
    case Operational = 'operational';
    case Down = 'down';
    case Maintenance = 'maintenance';

    /**
     * UI 表示用の日本語ラベルを返す。
     */
    public function label(): string
    {
        return match ($this) {
            self::Operational => '稼働中',
            self::Down => '停止中',
            self::Maintenance => '保全中',
        };
    }

    /**
     * ステータスバッジ用の Tailwind カラーキーを返す。
     */
    public function color(): string
    {
        return match ($this) {
            self::Operational => 'green',
            self::Down => 'red',
            self::Maintenance => 'amber',
        };
    }
}
