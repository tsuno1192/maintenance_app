<?php

namespace Database\Factories;

use App\Models\Memo;
use App\Models\MemoImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MemoImage>
 */
class MemoImageFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義する。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'memo_'.Str::uuid().'.jpg';

        return [
            'memo_id' => Memo::factory(),
            'file_path' => 'memos/'.fake()->date('Y/m/d').'/'.$name,
            'original_name' => fake()->word().'.jpg',
        ];
    }
}
