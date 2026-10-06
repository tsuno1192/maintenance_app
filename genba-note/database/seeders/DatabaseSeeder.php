<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\TroubleSeeder;

/**
 * アプリケーション全体の初期／テストデータ投入エントリポイント。
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MachineSeeder::class,
            MemoSeeder::class,
            ToolSeeder::class,
            TroubleSeeder::class,
        ]);
    }
}
