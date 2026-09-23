<?php

namespace App\Models;

use App\Enums\MemoPriority;
use App\Enums\MemoShift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Memo extends Model
{
    /** @use HasFactory<\Database\Factories\MemoFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'machine_id',
        'title',
        'body',
        'shift',
        'priority',
        'category',
        'acknowledged_by',
        'acknowledged_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shift' => MemoShift::class,
            'priority' => MemoPriority::class,
            'acknowledged_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(MemoImage::class);
    }

    public function isAcknowledged(): bool
    {
        return $this->acknowledged_at !== null;
    }

    protected static function booted(): void
    {
        static::deleting(function (Memo $memo): void {
            $memo->images()->each(fn (MemoImage $image) => $image->delete());
        });
    }
}
