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
            'category' => fake()->randomElement(['Utilities', 'Maintenance', 'Supplies', 'Staff', 'Other']),
            'amount' => fake()->randomFloat(2, 50, 10000),
            'expense_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'description' => fake()->sentence(),
            'receipt_path' => null,
            'status' => fake()->randomElement(['pending_approval', 'approved', 'rejected']),
            'rejection_reason' => null,
            'notes' => fake()->optional()->sentence(),
            'submitted_by' => User::factory(),
            'submitted_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}