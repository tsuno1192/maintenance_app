<?php

namespace Database\Seeders;

use App\Enums\MachineStatus;
use App\Models\Machine;
use Illuminate\Database\Seeder;

/**
 * 設備マスタのテストデータを投入する。
 */
class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $preset = [
            [
                'name' => 'プレス機 #1',
                'qr_identifier' => 'MCH-0001',
                'manual_url' => 'https://example.com/manuals/press-1',
                'location' => 'A棟-1F',
                'status' => MachineStatus::Operational,
            ],
            [
                'name' => '旋盤 #2',
                'qr_identifier' => 'MCH-0002',
                'manual_url' => null,
                'location' => 'A棟-2F',
                'status' => MachineStatus::Maintenance,
            ],
            [
                'name' => '搬送コンベア #3',
                'qr_identifier' => 'MCH-0003',
                'manual_url' => 'https://example.com/manuals/conveyor-3',
                'location' => 'B棟-1F',
                'status' => MachineStatus::Down,
            ],
        ];

        foreach ($preset as $machine) {
            Machine::query()->create($machine);
        }

        Machine::factory()->count(5)->create();
    }
}
