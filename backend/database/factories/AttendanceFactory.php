<?php


namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_id' => User::factory(),
            'service_type' => fake()->randomElement(['Sunday', 'Wednesday', 'Friday', 'Special']),
            'service_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'count' => 1,
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'needs_revision']),
            'submitted_by' => User::factory(),
            'submitted_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            'notes' => fake()->optional()->sentence(),
            'resubmitted_from' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}