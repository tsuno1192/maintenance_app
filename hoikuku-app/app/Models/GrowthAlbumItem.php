<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowthAlbumItem extends Model
{
    protected $fillable = [
        'growth_album_id',
        'documentation_id',
        'score',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'float',
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(GrowthAlbum::class, 'growth_album_id');
    }

    public function documentation(): BelongsTo
    {
        return $this->belongsTo(Documentation::class);
    }
}
