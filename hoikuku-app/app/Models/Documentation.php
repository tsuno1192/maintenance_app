<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documentation extends Model
{
    use HasFactory;

    public const FACE_PENDING = 'pending';

    public const FACE_MATCHED = 'matched';

    public const FACE_UNMATCHED = 'unmatched';

    public const FACE_MANUAL = 'manual';

    public const FACE_SKIPPED = 'skipped';

    protected $fillable = [
        'child_id',
        'suggested_child_id',
        'user_id',
        'image_path',
        'face_match_status',
        'face_match_confidence',
        'face_matched_at',
        'ai_episode_title',
        'ai_body',
        'non_cognitive_skill',
    ];

    protected function casts(): array
    {
        return [
            'face_match_confidence' => 'float',
            'face_matched_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function suggestedChild(): BelongsTo
    {
        return $this->belongsTo(Child::class, 'suggested_child_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function needsFaceReview(): bool
    {
        return in_array($this->face_match_status, [self::FACE_PENDING, self::FACE_UNMATCHED], true)
            || $this->child_id === null;
    }
}
