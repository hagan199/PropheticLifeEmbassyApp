<?php

namespace Database\Seeders;

use App\Models\FollowUp;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Database\Seeder;

class VisitorSeeder extends Seeder
{
  public function run(): void
  {
    $prUser = User::where('role', 'pr_follow_up')->first();

    // Create visitors with follow-ups
    Visitor::factory(50)->create([
      'created_by' => $prUser->id,
    ])->each(function ($visitor) use ($prUser) {
      // Add 1-3 follow-ups for some visitors
      if (rand(0, 1)) {
        FollowUp::factory(rand(1, 3))->create([
          'visitor_id' => $visitor->id,
          'logged_by' => $prUser->id,
        ]);
      }
    });

    // Create some partners
    Visitor::factory(15)->partner()->create([
      'created_by' => $prUser->id,
    ]);
  }
}
