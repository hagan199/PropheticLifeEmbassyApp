<?php


namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseType>
 */
class ExpenseTypeFactory extends Factory
{
    public function definition(): array
    {
        $types = [
            'Utilities', 'Maintenance', 'Supplies', 'Staff Salary', 
            'Transport', 'Events', 'Equipment', 'Miscellaneous'
        ];

        return [
            'name' => fake()->unique()->randomElement($types),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}