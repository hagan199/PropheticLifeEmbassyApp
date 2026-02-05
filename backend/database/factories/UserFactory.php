<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '+233' . fake()->numerify('#########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement(['admin', 'pastor', 'usher', 'finance', 'pr_follow_up', 'department_leader']),
            'department_id' => null,
            'has_2fa' => false,
            'can_approve_attendance' => false,
            'status' => 'active',
            'avatar' => null,
            'last_login_at' => fake()->optional()->dateTimeThisMonth(),
            'last_login_ip' => fake()->optional()->ipv4(),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'admin',
            'has_2fa' => true,
            'can_approve_attendance' => true,
        ]);
    }

    public function pastor(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'pastor',
            'can_approve_attendance' => true,
        ]);
    }

    public function usher(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'usher',
        ]);
    }

    public function finance(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'finance',
            'has_2fa' => true,
        ]);
    }

    public function prFollowUp(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'pr_follow_up',
        ]);
    }

    public function departmentLeader(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'department_leader',
            'can_approve_attendance' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
            'deactivated_at' => now(),
            'deactivation_reason' => fake()->sentence(),
        ]);
    }
}
