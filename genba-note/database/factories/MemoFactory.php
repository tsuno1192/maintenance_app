<?php

namespace Database\Factories;

use App\Enums\MemoStatus;
use App\Models\Machine;
use App\Models\Memo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Memo>
 */
class MemoFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義する。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'machine_id' => Machine::factory(),
            'user_id' => User::factory(),
            'message' => fake()->realText(120),
            'tags' => fake()->randomElements(
                ['異音', '油漏れ', '温度上昇', '振動', '定期点検', '部品交換', '安全'],
                fake()->numberBetween(1, 3)
            ),
            'status' => fake()->randomElement(MemoStatus::cases()),
        ];
    }

    /**
     * 未対応ステータスにする。
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MemoStatus::Pending,
        ]);
    }
}
