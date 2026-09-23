<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 申し送り API リソース。
 *
 * @mixin \App\Models\Memo
 */
class MemoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'machine_id' => $this->machine_id,
            'user_id' => $this->user_id,
            'message' => $this->message,
            'tags' => $this->tags ?? [],
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'machine' => new MachineResource($this->whenLoaded('machine')),
            'user' => new UserResource($this->whenLoaded('user')),
            'images' => MemoImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
