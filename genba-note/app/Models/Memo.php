<?php

namespace App\Models;

use App\Enums\MemoStatus;
use Database\Factories\MemoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 設備に対する申し送り（現場メモ）。
 *
 * @property string $id
 * @property string $machine_id
 * @property string $user_id
 * @property string $message
 * @property array<int, string>|null $tags
 * @property MemoStatus $status
 */
#[Fillable(['machine_id', 'user_id', 'message', 'tags', 'status'])]
class Memo extends Model
{
    /** @use HasFactory<MemoFactory> */
    use HasFactory, HasUuids;

    /**
     * 属性キャスト定義。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'status' => MemoStatus::class,
        ];
    }

    /**
     * 対象設備。
     *
     * @return BelongsTo<Machine, $this>
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * 投稿者。
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 添付画像一覧。
     *
     * @return HasMany<MemoImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(MemoImage::class);
    }
}
