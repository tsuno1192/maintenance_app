<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_period_id',
        'target_date',
        'shift_pattern_id',
    ];
}