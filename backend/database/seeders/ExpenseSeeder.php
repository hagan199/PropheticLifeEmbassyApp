<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
  public function run(): void
  {
    $expenseTypes = ExpenseType::all();
    $financeUser = User::where('role', 'finance')->first();
    $approver = User::where('role', 'admin')->first();

    // Generate approved expenses for past 3 months
    for ($i = 0; $i < 12; $i++) {
      $date = now()->subWeeks($i);

      // 2-4 expenses per week
      for ($j = 0; $j < rand(2, 4); $j++) {
        $expenseType = $expenseTypes->random();

        Expense::create([
          'expense_type_id' => $expenseType->id,
          'category' => $expenseType->name,
          'amount' => fake()->randomFloat(2, 100, 5000),
          'expense_date' => $date->format('Y-m-d'),
          'description' => fake()->sentence(),
          'status' => 'approved',
          'submitted_by' => $financeUser->id,
          'submitted_at' => $date,
          'approved_by' => $approver->id,
          'approved_at' => $date->copy()->addDay(),
        ]);
      }
    }

    // Some pending expenses
    foreach (range(1, 5) as $i) {
      $expenseType = $expenseTypes->random();

      Expense::create([
        'expense_type_id' => $expenseType->id,
        'category' => $expenseType->name,
        'amount' => fake()->randomFloat(2, 100, 3000),
        'expense_date' => now()->subDays(rand(1, 7))->format('Y-m-d'),
        'description' => fake()->sentence(),
        'status' => 'pending_approval',
        'submitted_by' => $financeUser->id,
        'submitted_at' => now(),
      ]);
    }
  }
}
