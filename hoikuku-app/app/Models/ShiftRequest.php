<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'target_date',
        'request_type',
        'shift_pattern_id',
        'start_time',
        'end_time',
        'memo',
    ];

    protected $casts = [
        'target_date' => 'date',
        'request_type' => 'integer',
    ];

    /**
     * 固定パターンマスタとのリレーション
     */
    public function shiftPattern(): BelongsTo
    {
        return $this->belongsTo(ShiftPattern::class);
    }
}
