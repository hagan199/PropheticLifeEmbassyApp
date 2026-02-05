<?php

namespace Database\Seeders;

use App\Models\Contribution;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContributionSeeder extends Seeder
{
  public function run(): void
  {
    $members = User::where('role', '!=', 'admin')->get();
    $financeUser = User::where('role', 'finance')->first();
    $approver = User::where('role', 'admin')->first();

    // Generate contributions for past 3 months
    for ($i = 0; $i < 12; $i++) {
      $date = now()->subWeeks($i)->startOfWeek()->addDays(6); // Sunday

      // Tithes
      foreach ($members->random(rand(10, 20)) as $member) {
        Contribution::create([
          'member_id' => $member->id,
          'type' => 'tithe',
          'amount' => fake()->randomFloat(2, 50, 500),
          'payment_method' => fake()->randomElement(['cash', 'momo', 'bank']),
          'reference' => fake()->optional()->uuid(),
          'date' => $date->format('Y-m-d'),
          'contribution_month' => $date->format('Y-m-01'),
          'status' => 'approved',
          'recorded_by' => $financeUser->id,
          'approved_by' => $approver->id,
          'approved_at' => $date->copy()->addDays(1),
        ]);
      }

      // Offerings
      foreach ($members->random(rand(15, 30)) as $member) {
        Contribution::create([
          'member_id' => $member->id,
          'type' => 'offering',
          'amount' => fake()->randomFloat(2, 10, 200),
          'payment_method' => fake()->randomElement(['cash', 'momo']),
          'date' => $date->format('Y-m-d'),
          'status' => 'approved',
          'recorded_by' => $financeUser->id,
          'approved_by' => $approver->id,
          'approved_at' => $date->copy()->addDays(1),
        ]);
      }
    }

    // Some pending contributions
    Contribution::factory(10)->create([
      'status' => 'pending_review',
      'recorded_by' => $financeUser->id,
    ]);
  }
}
