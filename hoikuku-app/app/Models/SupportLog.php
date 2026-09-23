<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class SupportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'user_id',
        'target_date',
        'daily_status',
        'parent_sharing',
        'staff_handover',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}