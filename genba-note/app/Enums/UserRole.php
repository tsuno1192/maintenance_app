<?php

namespace App\Enums;

/**
 * アプリケーション利用者の権限ロール。
 *
 * 管理者はマスタ管理・ユーザー管理を行い、
 * 作業員は現場での申し送り・工具貸出など日常業務を行う。
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Worker = 'worker';

    /**
     * UI 表示用の日本語ラベルを返す。
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => '管理者',
            self::Worker => '作業員',
        };
    }
}
