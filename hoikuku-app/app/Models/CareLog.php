<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareLog extends Model
{
    public const TYPE_DIAPER = 'diaper';

    public const TYPE_MILK = 'milk';

    public const TYPE_MEAL = 'meal';

    protected $fillable = [
        'child_id',
        'user_id',
        'logged_at',
        'type',
        'diaper_status',
        'milk_ml',
        'meal_amount',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
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
}
