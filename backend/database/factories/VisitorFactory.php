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
      'name' => $this->faker->name(),
      'phone' => '+233' . $this->faker->numerify('#########'),
      'email' => $this->faker->optional()->safeEmail(),
      'category' => $this->faker->randomElement(['Visitor', 'Partner']),
      'source' => $this->faker->randomElement(['Friend', 'Social Media', 'Walk-in', 'Other']),
      'source_detail' => $this->faker->optional()->sentence(),
      'ministry_interest' => $this->faker->randomElements(['Youth', 'Counseling', 'Giving', 'Fellowship', 'Other'], rand(1, 3)),
      'initial_notes' => $this->faker->optional()->paragraph(),
      'first_visit_date' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
      'status' => $this->faker->randomElement(['not_contacted', 'contacted', 'engaged', 'converted']),
      'next_follow_up_date' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d') : null,
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
      'next_follow_up_date' => $this->faker->dateTimeBetween('now', '+1 week')->format('Y-m-d'),
    ]);
  }
}
