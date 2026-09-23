<?php

namespace App\Models;

use App\Enums\ToolStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    /** @use HasFactory<\Database\Factories\ToolFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'category',
        'location',
        'status',
        'quantity',
        'manufacturer',
        'purchased_on',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ToolStatus::class,
            'purchased_on' => 'date',
            'quantity' => 'integer',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ToolLog::class)->latest();
    }

    public function displayName(): string
    {
        return $this->code.' '.$this->name;
    }
}
