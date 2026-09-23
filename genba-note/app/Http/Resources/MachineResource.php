<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 設備 API リソース。
 *
 * @mixin \App\Models\Machine
 */
class MachineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'qr_identifier' => $this->qr_identifier,
            'manual_url' => $this->manual_url,
            'location' => $this->location,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'memos_count' => $this->whenCounted('memos'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
