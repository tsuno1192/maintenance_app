<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 既存のデータを残したい場合は truncate を外してください
        // User::truncate();

        // 20人分のスタッフを作成
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'staff_id' => 'STF' . str_pad($i, 3, '0', STR_PAD_LEFT), // 例: STF001, STF002 ...
                'name' => 'スタッフ ' . $i,
                'email' => 'staff' . $i . '@example.com',
                'password' => Hash::make('password123'), // 共通のパスワード
                // もし role カラム等があればここで指定できます
                'role' => 'staff',
            ]);
        }
    }
}
