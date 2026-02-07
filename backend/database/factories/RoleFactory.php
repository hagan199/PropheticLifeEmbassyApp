<?php


namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'display_name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'is_system' => false,
        ];
    }

    public function system(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_system' => true,
        ]);
    }
}
