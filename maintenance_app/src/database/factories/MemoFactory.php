<?php

namespace Database\Factories;

use App\Enums\MemoPriority;
use App\Enums\MemoShift;
use App\Models\Memo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Memo>
 */
class MemoFactory extends Factory
{
    protected $model = Memo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'machine_id' => null,
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'shift' => MemoShift::Day,
            'priority' => MemoPriority::Normal,
            'category' => fake()->randomElement(['運転', '保全', '安全', 'その他']),
            'acknowledged_by' => null,
            'acknowledged_at' => null,
        ];
    }
}
