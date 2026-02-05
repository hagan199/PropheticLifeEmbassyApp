<?php


namespace Database\Factories;

use App\Models\User;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FollowUp>
 */
class FollowUpFactory extends Factory
{
  public function definition(): array
  {
    return [
      'visitor_id' => Visitor::factory(),
      'contact_method' => fake()->randomElement(['WhatsApp', 'SMS', 'Call', 'In-person']),
      'outcome_notes' => fake()->paragraph(),
      'status_after' => fake()->randomElement(['not_contacted', 'contacted', 'engaged', 'converted']),
      'next_follow_up_date' => fake()->boolean(60) ? fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d') : null,
      'logged_by' => User::factory(),
    ];
  }
}
