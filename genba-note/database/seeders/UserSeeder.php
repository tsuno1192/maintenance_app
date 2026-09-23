<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * 管理者・作業員のテストユーザーを投入する。
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => '現場管理者',
            'email' => 'admin@genba-note.test',
            'role' => UserRole::Admin,
        ]);

        User::factory()->worker()->create([
            'name' => '作業員 太郎',
            'email' => 'worker@genba-note.test',
            'role' => UserRole::Worker,
        ]);

        User::factory()->worker()->count(3)->create();
    }
}
