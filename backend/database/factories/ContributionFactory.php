<?php


namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contribution>
 */
class ContributionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_id' => User::factory(),
            'type' => fake()->randomElement(['tithe', 'offering', 'special_seed', 'building_fund', 'missions', 'welfare']),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'payment_method' => fake()->randomElement(['cash', 'momo', 'bank', 'cheque']),
            'reference' => fake()->optional()->uuid(),
            'mobile_number' => fake()->optional()->numerify('+233#########'),
            'date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'contribution_month' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-01'),
            'frequency' => fake()->optional()->randomElement(['weekly', 'monthly', 'as_able']),
            'expected_date' => null,
            'status' => fake()->randomElement(['pending_review', 'reviewed', 'approved', 'rejected', 'overdue']),
            'notes' => fake()->optional()->sentence(),
            'recorded_by' => User::factory(),
            'reviewed_by' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    public function tithe(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'tithe',
        ]);
    }

    public function offering(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'offering',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function momo(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'momo',
            'mobile_number' => '+233' . fake()->numerify('#########'),
            'reference' => fake()->uuid(),
        ]);
    }
}