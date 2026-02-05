<?php


namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
  public function definition(): array
  {
    $actions = ['create', 'update', 'delete', 'login', 'logout', 'approve', 'reject'];
    $entityTypes = ['users', 'attendance', 'contributions', 'expenses', 'visitors', 'broadcasts'];

    return [
      'user_id' => User::factory(),
      'action' => fake()->randomElement($actions),
      'entity_type' => fake()->randomElement($entityTypes),
      'entity_id' => Str::uuid(),
      'changes' => [
        'before' => ['status' => 'pending'],
        'after' => ['status' => 'approved'],
      ],
      'ip_address' => fake()->ipv4(),
      'user_agent' => fake()->userAgent(),
      'description' => fake()->sentence(),
    ];
  }
}
