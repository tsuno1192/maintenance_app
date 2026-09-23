<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * アプリケーション利用者（管理者／作業員）。
 *
 * UUID 主キーを採用し、role カラムで認可の基礎を担う。
 *
 * @property string $id
 * @property string $name
 * @property string $email
 * @property UserRole $role
 */
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    /**
     * 属性キャスト定義。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * このユーザーが投稿した申し送り一覧。
     *
     * @return HasMany<Memo, $this>
     */
    public function memos(): HasMany
    {
        return $this->hasMany(Memo::class);
    }

    /**
     * このユーザーの工具貸出／返却履歴。
     *
     * @return HasMany<ToolLog, $this>
     */
    public function toolLogs(): HasMany
    {
        return $this->hasMany(ToolLog::class);
    }

    /**
     * 管理者ロールかどうかを判定する。
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * 作業員ロールかどうかを判定する。
     */
    public function isWorker(): bool
    {
        return $this->role === UserRole::Worker;
    }
}
