<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'group_name',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
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

    public function reportedTroubles(): HasMany
    {
        return $this->hasMany(Trouble::class, 'reporter_user_id');
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    public function memos(): HasMany
    {
        return $this->hasMany(Memo::class);
    }

    public function toolLogs(): HasMany
    {
        return $this->hasMany(ToolLog::class);
    }

    public function acknowledgedMemos(): HasMany
    {
        return $this->hasMany(Memo::class, 'acknowledged_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
