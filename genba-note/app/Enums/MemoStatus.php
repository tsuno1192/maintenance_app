<?php

namespace App\Enums;

/**
 * 申し送り（メモ）の対応ステータス。
 */
enum MemoStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';

    /**
     * UI 表示用の日本語ラベルを返す。
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => '未対応',
            self::InProgress => '対応中',
            self::Resolved => '完了',
        };
    }

    /**
     * ステータスバッジ用の Tailwind カラーキーを返す。
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::InProgress => 'blue',
            self::Resolved => 'green',
        };
    }
}
