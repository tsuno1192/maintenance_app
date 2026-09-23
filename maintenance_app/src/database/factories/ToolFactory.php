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
    protected $model = Tool::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'TL-'.fake()->unique()->numerify('###'),
            'name' => fake()->randomElement(['トルクレンチ', '絶縁ドライバーセット', 'マルチメータ', 'チェーンブロック', '油圧ジャッキ']),
            'category' => fake()->randomElement(['手工具', '電動工具', '測定器', '揚重機', '安全保護具']),
            'location' => fake()->randomElement(['工具室A', '工具室B', '現場倉庫', '電気室']),
            'status' => ToolStatus::Available,
            'quantity' => fake()->numberBetween(1, 5),
            'manufacturer' => fake()->company(),
            'purchased_on' => fake()->dateTimeBetween('-5 years')->format('Y-m-d'),
            'notes' => null,
        ];
    }
}
