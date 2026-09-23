<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'target_date',
        'shift_pattern_id',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'target_date' => 'date',
        'status' => 'integer',
    ];

    /**
     * 固定パターンマスタとのリレーション
     */
    public function shiftPattern(): BelongsTo
    {
        return $this->belongsTo(ShiftPattern::class);
    }

    /**
     * スタッフ（ユーザー）とのリレーション
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
