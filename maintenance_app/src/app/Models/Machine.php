<?php

namespace App\Models;

use App\Enums\MachineStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    /** @use HasFactory<\Database\Factories\MachineFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'area',
        'category',
        'manufacturer',
        'model',
        'installed_on',
        'status',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MachineStatus::class,
            'installed_on' => 'date',
        ];
    }

    public function memos(): HasMany
    {
        return $this->hasMany(Memo::class);
    }

    public function troubles(): HasMany
    {
        return $this->hasMany(Trouble::class);
    }

    public function displayName(): string
    {
        return $this->code.' '.$this->name;
    }
}
