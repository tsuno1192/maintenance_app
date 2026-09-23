<?php

namespace Database\Seeders;

use App\Enums\ToolLogAction;
use App\Enums\ToolStatus;
use App\Models\Tool;
use App\Models\ToolLog;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * 工具マスタと貸出／返却履歴のテストデータを投入する。
 */
class ToolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $preset = [
            [
                'name' => 'トルクレンチ 50N·m',
                'serial_number' => 'TL-TW50-001',
                'status' => ToolStatus::Available,
                'current_location' => '工具室',
            ],
            [
                'name' => 'デジタルマルチメータ',
                'serial_number' => 'TL-DMM-014',
                'status' => ToolStatus::InUse,
                'current_location' => 'A棟現場',
            ],
            [
                'name' => '振動計',
                'serial_number' => 'TL-VIB-003',
                'status' => ToolStatus::Maintenance,
                'current_location' => '点検室',
            ],
        ];

        foreach ($preset as $tool) {
            Tool::query()->create($tool);
        }

        Tool::factory()->count(6)->create();

        $users = User::query()->get();
        $tools = Tool::query()->get();

        if ($users->isEmpty() || $tools->isEmpty()) {
            return;
        }

        foreach ($tools->take(4) as $tool) {
            ToolLog::factory()->create([
                'tool_id' => $tool->id,
                'user_id' => $users->random()->id,
                'action' => ToolLogAction::Borrowed,
                'notes' => '現場点検のため貸出',
            ]);

            if ($tool->status === ToolStatus::Available) {
                ToolLog::factory()->create([
                    'tool_id' => $tool->id,
                    'user_id' => $users->random()->id,
                    'action' => ToolLogAction::Returned,
                    'notes' => '返却完了',
                ]);
            }
        }
    }
}
