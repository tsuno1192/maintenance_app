<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactNote extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_POLISHED = 'polished';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'child_id',
        'user_id',
        'note_date',
        'raw_memo',
        'polished_body',
        'status',
        'shared_at',
    ];

    protected function casts(): array
    {
        return [
            'note_date' => 'date',
            'shared_at' => 'datetime',
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
