<?php

namespace App\Enums;

/**
 * 工具履歴における貸出／返却アクション。
 */
enum ToolLogAction: string
{
    case Borrowed = 'borrowed';
    case Returned = 'returned';

    /**
     * UI 表示用の日本語ラベルを返す。
     */
    public function label(): string
    {
        return match ($this) {
            self::Borrowed => '貸出',
            self::Returned => '返却',
        };
    }
}
