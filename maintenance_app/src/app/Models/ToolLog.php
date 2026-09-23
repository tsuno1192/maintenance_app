<?php

namespace App\Models;

use App\Enums\ToolLogAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolLog extends Model
{
    /** @use HasFactory<\Database\Factories\ToolLogFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tool_id',
        'user_id',
        'action',
        'quantity',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => ToolLogAction::class,
            'quantity' => 'integer',
        ];
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
