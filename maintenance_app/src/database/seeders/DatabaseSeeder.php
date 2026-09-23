<?php

namespace Database\Seeders;

use App\Enums\MemoPriority;
use App\Enums\ToolLogAction;
use App\Enums\ToolStatus;
use App\Enums\UserRole;
use App\Models\Machine;
use App\Models\Memo;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 初期ユーザーのパスワードはすべて password
        User::factory()->role(UserRole::Admin, '保全Gr')->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
        ]);

        $leader = User::factory()->role(UserRole::Leader, '運転Gr')->create([
            'name' => '運転リーダ',
            'email' => 'leader@example.com',
        ]);

        $user = User::factory()->role(UserRole::Discoverer, '運転Gr')->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $machines = collect([
            Machine::factory()->create([
                'code' => 'EQ-101',
                'name' => '反応槽A',
                'area' => '第1プラント',
                'category' => '静機器',
            ]),
            Machine::factory()->create([
                'code' => 'EQ-201',
                'name' => 'ポンプP-101',
                'area' => '第1プラント',
                'category' => '回転機',
            ]),
            Machine::factory()->create([
                'code' => 'EQ-301',
                'name' => 'コンプレッサC-1',
                'area' => 'ユーティリティ',
                'category' => '回転機',
            ]),
        ]);

        Memo::factory()->create([
            'user_id' => $user->id,
            'machine_id' => $machines[0]->id,
            'title' => '反応槽A 異音あり。次直で確認お願いします。',
            'body' => '21時頃から軸受付近で周期的な異音。振動値は許容内。翌朝に再確認してください。',
            'priority' => MemoPriority::Important,
            'category' => '運転',
        ]);

        Memo::factory()->create([
            'user_id' => $leader->id,
            'machine_id' => $machines[1]->id,
            'title' => 'P-101 グランド漏れ微量。経過観察。',
            'body' => '受け皿に滴下あり。今夜は増えていないため経過観察。保全へ連絡済み。',
            'category' => '保全',
        ]);

        $wrench = Tool::factory()->create([
            'code' => 'TL-001',
            'name' => 'トルクレンチ 1/2',
            'category' => '手工具',
            'location' => '工具室A',
            'status' => ToolStatus::InUse,
            'quantity' => 0,
        ]);

        Tool::factory()->create([
            'code' => 'TL-014',
            'name' => '絶縁ドライバーセット',
            'category' => '手工具',
            'location' => '電気室',
        ]);

        $wrench->logs()->create([
            'user_id' => $user->id,
            'action' => ToolLogAction::Checkout,
            'quantity' => 1,
            'note' => 'P-101 グランド増し締め',
        ]);
    }
}
