<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndividualSupportPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'support_goal',
        'start_date',
        'end_date',
        'specific_approaches',
        'evaluation',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }
}