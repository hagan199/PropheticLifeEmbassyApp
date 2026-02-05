<?php


namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Broadcast>
 */
class BroadcastFactory extends Factory
{
  public function definition(): array
  {
    $totalRecipients = fake()->numberBetween(10, 200);
    $deliveredCount = fake()->numberBetween(0, $totalRecipients);
    $failedCount = $totalRecipients - $deliveredCount;

    return [
      'recipient_group' => fake()->randomElement(['all_members', 'partnerships', 'department']),
      'department_id' => null,
      'channel' => fake()->randomElement(['whatsapp', 'sms']),
      'message' => fake()->paragraph(),
      'total_recipients' => $totalRecipients,
      'delivered_count' => $deliveredCount,
      'failed_count' => $failedCount,
      'delivery_rate' => $totalRecipients > 0 ? round(($deliveredCount / $totalRecipients) * 100, 2) : 0,
      'status' => fake()->randomElement(['pending', 'sent', 'partially_sent', 'failed', 'scheduled']),
      'scheduled_for' => null,
      'sent_at' => fake()->optional()->dateTimeThisMonth(),
      'error_reason' => null,
      'retry_count' => 0,
      'sender_id' => User::factory(),
    ];
  }

  public function sent(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'sent',
      'sent_at' => now(),
      'delivery_rate' => 100,
    ]);
  }

  public function scheduled(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'scheduled',
      'scheduled_for' => fake()->dateTimeBetween('now', '+1 week'),
    ]);
  }

  public function toDepartment(): static
  {
    return $this->state(fn(array $attributes) => [
      'recipient_group' => 'department',
      'department_id' => Department::factory(),
    ]);
  }
}
