<?php

namespace App\Models;

use Database\Factories\MemoImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * 申し送りに添付された画像のメタデータ。
 *
 * 実体ファイルは filesystems.media_disk 上に保存し、
 * ディスク切替（local/public → S3）に耐えるようパスのみを保持する。
 *
 * @property string $id
 * @property string $memo_id
 * @property string $file_path
 * @property string $original_name
 */
#[Fillable(['memo_id', 'file_path', 'original_name'])]
class MemoImage extends Model
{
    /** @use HasFactory<MemoImageFactory> */
    use HasFactory, HasUuids;

    /**
     * 親となる申し送り。
     *
     * @return BelongsTo<Memo, $this>
     */
    public function memo(): BelongsTo
    {
        return $this->belongsTo(Memo::class);
    }

    /**
     * 公開 URL を返す（ディスクが URL 生成可能な場合）。
     */
    public function url(): ?string
    {
        $disk = config('filesystems.media_disk', 'public');

        return Storage::disk($disk)->url($this->file_path);
    }
}
