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
    protected $model = Machine::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'EQ-'.fake()->unique()->numerify('###'),
            'name' => fake()->randomElement(['反応槽A', 'ポンプP-101', 'コンプレッサC-1', '冷却塔CT-2', '送風機F-12']),
            'area' => fake()->randomElement(['第1プラント', '第2プラント', 'ユーティリティ', '電気室']),
            'category' => fake()->randomElement(['回転機', '静機器', '電気設備', '計装設備']),
            'manufacturer' => fake()->company(),
            'model' => strtoupper(fake()->bothify('??-###')),
            'installed_on' => fake()->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
            'status' => MachineStatus::Running,
            'notes' => null,
        ];
    }
}
