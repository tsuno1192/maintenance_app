<?php

namespace App\Enums;

/**
 * 工具履歴における在庫調整・貸出・返却アクション。
 */
enum ToolLogAction: string
{
    case Adjust = 'adjust';
    case Checkout = 'checkout';
    case Checkin = 'checkin';

    /**
     * UI 表示用の日本語ラベルを返す。
     */
    public function label(): string
    {
        return match ($this) {
            self::Adjust => '在庫調整',
            self::Checkout => '貸出',
            self::Checkin => '返却',
        };
    }

    /**
     * アクション実行後の工具ステータスを返す。
     */
    public function resultingStatus(): ?string
    {
        return match ($this) {
            self::Checkout => 'in_use',   // 貸出時は「貸出中」
            self::Checkin => 'available', // 返却時は「利用可」
            self::Adjust => null,         // 調整時はステータス変更なし
        };
    }
}