<?php

namespace App\Models;

use App\Enums\ToolStatus;
use Database\Factories\ToolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 工具マスタ。
 *
 * @property string $id
 * @property string $name
 * @property string $serial_number
 * @property ToolStatus $status
 * @property string|null $current_location
 */
#[Fillable(['name', 'serial_number', 'status', 'current_location'])]
class Tool extends Model
{
    /** @use HasFactory<ToolFactory> */
    use HasFactory, HasUuids;

    /**
     * 属性キャスト定義。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ToolStatus::class,
        ];
    }

    /**
     * この工具の貸出／返却履歴。
     *
     * @return HasMany<ToolLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ToolLog::class);
    }
}
