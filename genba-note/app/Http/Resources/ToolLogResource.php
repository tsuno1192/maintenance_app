<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 工具履歴 API リソース。
 *
 * @mixin \App\Models\ToolLog
 */
class ToolLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tool_id' => $this->tool_id,
            'user_id' => $this->user_id,
            'action' => $this->action?->value,
            'action_label' => $this->action?->label(),
            'notes' => $this->notes,
            'tool' => new ToolResource($this->whenLoaded('tool')),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
