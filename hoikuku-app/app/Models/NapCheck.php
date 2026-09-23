<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NapCheck extends Model
{
    protected $fillable = [
        'child_id',
        'user_id',
        'checked_at',
        'posture',
        'breathing_status',
        'sensor_source',
        'alert_level',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alert(): HasOne
    {
        return $this->hasOne(NapAlert::class);
    }

    public function isCritical(): bool
    {
        return $this->alert_level === 'critical'
            || $this->breathing_status === 'none'
            || $this->posture === 'stomach';
    }
}
