<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_ticket_id',
        'user_id',
        'body',
        'original_body',
        'is_read',
        'ai_status',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ConsultationTicket::class, 'consultation_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
