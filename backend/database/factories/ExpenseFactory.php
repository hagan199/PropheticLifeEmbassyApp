<?php


namespace Database\Factories;

use App\Models\ExpenseType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'expense_type_id' => ExpenseType::factory(),
            'category' => $this->faker->randomElement(['Utilities', 'Maintenance', 'Supplies', 'Staff', 'Other']),
            'amount' => $this->faker->randomFloat(2, 50, 10000),
            'expense_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'description' => $this->faker->sentence(),
            'receipt_path' => null,
            'status' => $this->faker->randomElement(['pending_approval', 'approved', 'rejected']),
            'rejection_reason' => null,
            'notes' => $this->faker->optional()->sentence(),
            'submitted_by' => User::factory(),
            'submitted_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }
}
