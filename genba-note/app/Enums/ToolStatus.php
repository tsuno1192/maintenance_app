<?php

namespace App\Enums;

/**
 * 工具の在庫・利用ステータス。
 */
enum ToolStatus: string
{
    case Available = 'available';
    case InUse = 'in_use';
    case Maintenance = 'maintenance';

    /**
     * UI 表示用の日本語ラベルを返す。
     */
    public function label(): string
    {
        return match ($this) {
            self::Available => '利用可',
            self::InUse => '貸出中',
            self::Maintenance => '点検中',
        };
    }

    /**
     * ステータスバッジ用の Tailwind カラーキーを返す。
     */
    public function color(): string
    {
        return match ($this) {
            self::Available => 'green',
            self::InUse => 'blue',
            self::Maintenance => 'amber',
        };
    }
}
