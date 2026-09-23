<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShiftPattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
    ];

    /**
     * シフト希望とのリレーション
     */
    public function shiftRequests(): HasMany
    {
        return $this->hasMany(ShiftRequest::class);
    }

    /**
     * シフトパターンが持つ資格（多対多）
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(
            Skill::class,
            'shift_pattern_skills',
            'shift_pattern_id',
            'skill_id'
        )->withPivot('required_count'); // 必要人数（required_count）も一緒に取得できるようにする
    }
}
