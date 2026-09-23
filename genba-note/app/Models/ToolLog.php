<?php

namespace App\Models;

use App\Enums\ToolLogAction;
use Database\Factories\ToolLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 工具の貸出／返却履歴。
 *
 * @property string $id
 * @property string $tool_id
 * @property string $user_id
 * @property ToolLogAction $action
 * @property string|null $notes
 */
#[Fillable(['tool_id', 'user_id', 'action', 'notes'])]
class ToolLog extends Model
{
    /** @use HasFactory<ToolLogFactory> */
    use HasFactory, HasUuids;

    /**
     * 属性キャスト定義。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => ToolLogAction::class,
        ];
    }

    /**
     * 対象工具。
     *
     * @return BelongsTo<Tool, $this>
     */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    /**
     * 操作を行ったユーザー。
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
