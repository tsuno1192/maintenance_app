<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 申し送り画像 API リソース。
 *
 * @mixin \App\Models\MemoImage
 */
class MemoImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_path' => $this->file_path,
            'original_name' => $this->original_name,
            'url' => $this->url(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
