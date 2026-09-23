<?php

namespace Database\Factories;

use App\Enums\MachineStatus;
use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Machine>
 */
class MachineFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義する。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $seq = fake()->unique()->numerify('####');

        return [
            'name' => fake()->randomElement(['プレス機', '旋盤', '搬送コンベア', '溶接ロボット', '検査装置']).' '.$seq,
            'qr_identifier' => 'MCH-'.$seq,
            'manual_url' => fake()->optional()->url(),
            'location' => fake()->randomElement(['A棟-1F', 'A棟-2F', 'B棟-1F', 'B棟-屋外', '倉庫']),
            'status' => fake()->randomElement(MachineStatus::cases()),
        ];
    }

    /**
     * 稼働中ステータスにする。
     */
    public function operational(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MachineStatus::Operational,
        ]);
    }
}
