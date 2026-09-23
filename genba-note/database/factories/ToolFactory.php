<?php

namespace Database\Factories;

use App\Enums\ToolStatus;
use App\Models\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tool>
 */
class ToolFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義する。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $seq = fake()->unique()->bothify('??##');

        return [
            'name' => fake()->randomElement(['トルクレンチ', 'デジタルマルチメータ', '油圧ジャッキ', '絶縁ドライバーセット', '振動計']),
            'serial_number' => 'TL-'.$seq,
            'status' => fake()->randomElement(ToolStatus::cases()),
            'current_location' => fake()->randomElement(['工具室', 'A棟現場', 'B棟現場', '点検室', null]),
        ];
    }

    /**
     * 利用可能ステータスにする。
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ToolStatus::Available,
            'current_location' => '工具室',
        ]);
    }
}
