<?php

namespace App\Models;

use App\Enums\MachineStatus;
use Database\Factories\MachineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 工場設備（マシン）マスタ。
 *
 * @property string $id
 * @property string $name
 * @property string $qr_identifier
 * @property string|null $manual_url
 * @property string|null $location
 * @property MachineStatus $status
 */
#[Fillable(['name', 'qr_identifier', 'manual_url', 'location', 'status'])]
class Machine extends Model
{
    /** @use HasFactory<MachineFactory> */
    use HasFactory, HasUuids;

    /**
     * 属性キャスト定義。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MachineStatus::class,
        ];
    }

    /**
     * この設備に紐づく申し送り一覧。
     *
     * @return HasMany<Memo, $this>
     */
    public function memos(): HasMany
    {
        return $this->hasMany(Memo::class);
    }
}
