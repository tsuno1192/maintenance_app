<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageCushionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_id',
        'is_emotional',
        'detected_reason',
    ];
}