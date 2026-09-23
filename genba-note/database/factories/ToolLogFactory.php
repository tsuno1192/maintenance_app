<?php

namespace Database\Factories;

use App\Enums\ToolLogAction;
use App\Models\Tool;
use App\Models\ToolLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ToolLog>
 */
class ToolLogFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義する。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tool_id' => Tool::factory(),
            'user_id' => User::factory(),
            'action' => fake()->randomElement(ToolLogAction::cases()),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
