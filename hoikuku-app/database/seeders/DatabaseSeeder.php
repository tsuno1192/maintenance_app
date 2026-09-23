<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // すでに存在していなければ管理者を作成
        User::firstOrCreate(
            ['email' => 'admin@example.com'], // 重複防止用メールアドレス
            [
                'name' => '管理者',
                'staff_id' => 'ADMIN001',      // 必要に応じて
                'password' => Hash::make('password123'), // 任意のパスワード
                'role' => 'admin',             // 管理者権限を付与
            ]
        );

        $this->call([
            StaffSeeder::class,
        ]);

        // SkillSeederを呼び出す
        $this->call([
            SkillSeeder::class,
        ]);

         // ChildSeederを呼び出す
        $this->call([
            ChildSeeder::class,
        ]);

        // ここに追加
        $this->call([
            ChildAllergySeeder::class,
        ]);
    }
}
