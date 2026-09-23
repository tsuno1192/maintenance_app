<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Skill; // Skillモデルを使用する宣言

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 事前に入力しておきたい資格・スキル名を定義
        $skills = [
            ['name' => '資格A'],
            ['name' => '資格B'],
            ['name' => '資格C'],
            ['name' => '資格D'],
            ['name' => '資格C'],
           
        ];

        foreach ($skills as $skill) {
            Skill::create($skill);
        }
    }
}