<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Child;

class ChildSeeder extends Seeder
{
    public function run(): void
    {
        Child::create([
            'child_id' => 'C-001',
            'name' => '山田 太郎',
            'birth_date' => '2021-04-01',
            'diagnosis' => '自閉スペクトラム症',
            'sensory_tendencies' => '大きな音が苦手',
            'panic_response_steps' => '静かな別室に移動して落ち着かせる',
            //'allergies' => '卵',
            //'daily_precautions' => 'おやつの前に手を洗う声かけが必要',
        ]);

        Child::create([
            'child_id' => 'C-002',
            'name' => '佐藤 花子',
            'birth_date' => '2022-05-15',
            'diagnosis' => null,
            'sensory_tendencies' => '特になし',
            'panic_response_steps' => '特になし',
            //'allergies' => 'なし',
            //'daily_precautions' => '特になし',
        ]);
    }
}