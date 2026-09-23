<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\ChildAllergy;
use Illuminate\Database\Seeder;

class ChildAllergySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 子どもデータを数件作成（または既存データを取得）
        $child1 = Child::create([
            'child_id' => 'CH001',
            'name' => 'テスト 太郎',
            'birth_date' => '2020-04-01',
            'diagnosis' => 'なし',
        ]);

        $child2 = Child::create([
            'child_id' => 'CH002',
            'name' => 'テスト 花子',
            'birth_date' => '2021-05-15',
            'diagnosis' => 'なし',
        ]);

      
    }
}