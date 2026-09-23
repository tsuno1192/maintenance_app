<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MemoImage extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'memo_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function memo(): BelongsTo
    {
        return $this->belongsTo(Memo::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (MemoImage $image): void {
            if ($image->path) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }
}
