<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Discoverer,
            'group_name' => '運転Gr',
            'remember_token' => Str::random(10),
        ];
    }

    public function role(UserRole $role, ?string $group = null): static
    {
        return $this->state(fn () => [
            'role' => $role,
            'group_name' => $group ?? match ($role) {
                UserRole::MaintenanceStaff,
                UserRole::MaintenanceLeader,
                UserRole::MaintenanceManager => '保全Gr',
                default => '運転Gr',
            },
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
