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
      'name' => $this->faker->unique()->word() . '.' . $this->faker->word(),
      'display_name' => $this->faker->words(3, true),
      'module' => $this->faker->randomElement($modules),
      'description' => $this->faker->sentence(),
    ];
  }
}
