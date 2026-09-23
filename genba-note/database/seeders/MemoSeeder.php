<?php

namespace Database\Seeders;

use App\Enums\MemoStatus;
use App\Models\Machine;
use App\Models\Memo;
use App\Models\MemoImage;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * 申し送りと添付画像メタデータのテストデータを投入する。
 *
 * 実ファイルは生成せず、パス情報のみをシードする。
 */
class MemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();
        $machines = Machine::query()->get();

        if ($users->isEmpty() || $machines->isEmpty()) {
            return;
        }

        foreach ($machines->take(5) as $machine) {
            $memo = Memo::factory()->create([
                'machine_id' => $machine->id,
                'user_id' => $users->random()->id,
                'status' => MemoStatus::Pending,
                'message' => "{$machine->name} で異音を確認。点検をお願いします。",
                'tags' => ['異音', '点検依頼'],
            ]);

            MemoImage::factory()->count(2)->create([
                'memo_id' => $memo->id,
            ]);
        }

        Memo::factory()
            ->count(8)
            ->state(fn () => [
                'machine_id' => $machines->random()->id,
                'user_id' => $users->random()->id,
            ])
            ->create();
    }
}
