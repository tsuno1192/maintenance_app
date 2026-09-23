<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealServiceCheck extends Model
{
    protected $fillable = [
        'child_id',
        'user_id',
        'meal_type',
        'menu_allergens',
        'matched_allergens',
        'alert_triggered',
        'acknowledged',
        'served_at',
    ];

    protected function casts(): array
    {
        return [
            'menu_allergens' => 'array',
            'matched_allergens' => 'array',
            'alert_triggered' => 'boolean',
            'acknowledged' => 'boolean',
            'served_at' => 'datetime',
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
