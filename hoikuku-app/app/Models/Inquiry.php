<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'user_id',
        'original_message',
        'mild_message',
        'ai_auto_response',
        'status',
        'ai_status',
    ];
}