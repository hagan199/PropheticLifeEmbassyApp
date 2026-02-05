<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visitor>
 */
class VisitorFactory extends Factory
{
  public function definition(): array
  {
    return [
      'name' => fake()->name(),
      'phone' => '+233' . fake()->numerify('#########'),
      'email' => fake()->optional()->safeEmail(),
      'category' => fake()->randomElement(['Visitor', 'Partner']),
      'source' => fake()->randomElement(['Friend', 'Social Media', 'Walk-in', 'Other']),
      'source_detail' => fake()->optional()->sentence(),
      'ministry_interest' => fake()->randomElements(['Youth', 'Counseling', 'Giving', 'Fellowship', 'Other'], rand(1, 3)),
      'initial_notes' => fake()->optional()->paragraph(),
      'first_visit_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
      'status' => fake()->randomElement(['not_contacted', 'contacted', 'engaged', 'converted']),
      'next_follow_up_date' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d') : null,
      'created_by' => User::factory(),
    ];
  }

  public function partner(): static
  {
    return $this->state(fn(array $attributes) => [
      'category' => 'Partner',
    ]);
  }

  public function needsFollowUp(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'contacted',
      'next_follow_up_date' => fake()->dateTimeBetween('now', '+1 week')->format('Y-m-d'),
    ]);
  }
}
