<?php


namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
  public function definition(): array
  {
    $modules = ['users', 'attendance', 'contributions', 'expenses', 'visitors', 'broadcasts', 'reports'];

    return [
      'name' => fake()->unique()->word() . '.' . fake()->word(),
      'display_name' => fake()->words(3, true),
      'module' => fake()->randomElement($modules),
      'description' => fake()->sentence(),
    ];
  }
}
