<?php


namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
  public function run(): void
  {
    $users = User::where('role', '!=', 'admin')->get();
    $ushers = User::where('role', 'usher')->get();
    $approvers = User::where('can_approve_attendance', true)->get();

    // Generate attendance for past 12 Sundays
    for ($i = 0; $i < 12; $i++) {
      $serviceDate = now()->subWeeks($i)->startOfWeek()->addDays(6); // Sunday

      foreach ($users->random(rand(20, 35)) as $user) {
        $status = fake()->randomElement(['approved', 'approved', 'approved', 'pending']);

        Attendance::create([
          'member_id' => $user->id,
          'service_type' => 'Sunday',
          'service_date' => $serviceDate->format('Y-m-d'),
          'count' => 1,
          'status' => $status,
          'submitted_by' => $ushers->random()->id,
          'submitted_at' => $serviceDate->copy()->addHours(rand(10, 14)),
          'approved_by' => $status === 'approved' ? $approvers->random()->id : null,
          'approved_at' => $status === 'approved' ? $serviceDate->copy()->addHours(rand(15, 20)) : null,
          'notes' => fake()->optional(0.2)->sentence(),
        ]);
      }
    }

    // Add some Wednesday services
    for ($i = 0; $i < 8; $i++) {
      $serviceDate = now()->subWeeks($i)->startOfWeek()->addDays(2); // Wednesday

      foreach ($users->random(rand(10, 20)) as $user) {
        Attendance::create([
          'member_id' => $user->id,
          'service_type' => 'Wednesday',
          'service_date' => $serviceDate->format('Y-m-d'),
          'count' => 1,
          'status' => 'approved',
          'submitted_by' => $ushers->random()->id,
          'submitted_at' => $serviceDate->copy()->addHours(rand(18, 20)),
          'approved_by' => $approvers->random()->id,
          'approved_at' => $serviceDate->copy()->addHours(rand(20, 22)),
        ]);
      }
    }
  }
}
