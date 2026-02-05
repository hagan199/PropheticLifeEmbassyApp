<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  public function run(): void
  {
    // Create Admin
    $admin = User::create([
      'name' => 'System Administrator',
      'phone' => '+233200000001',
      'email' => 'admin@pleapp.com',
      'password' => Hash::make('password'),
      'role' => 'admin',
      'has_2fa' => true,
      'can_approve_attendance' => true,
      'status' => 'active',
    ]);

    // Create Pastor
    $pastor = User::create([
      'name' => 'Pastor John Doe',
      'phone' => '+233200000002',
      'email' => 'pastor@pleapp.com',
      'password' => Hash::make('password'),
      'role' => 'pastor',
      'can_approve_attendance' => true,
      'status' => 'active',
    ]);

    // Create Finance Officer
    User::create([
      'name' => 'Finance Officer',
      'phone' => '+233200000003',
      'email' => 'finance@pleapp.com',
      'password' => Hash::make('password'),
      'role' => 'finance',
      'has_2fa' => true,
      'status' => 'active',
    ]);

    // Create Usher
    User::create([
      'name' => 'Head Usher',
      'phone' => '+233200000004',
      'email' => 'usher@pleapp.com',
      'password' => Hash::make('password'),
      'role' => 'usher',
      'status' => 'active',
    ]);

    // Create PR/Follow-up
    User::create([
      'name' => 'PR Coordinator',
      'phone' => '+233200000005',
      'email' => 'pr@pleapp.com',
      'password' => Hash::make('password'),
      'role' => 'pr_follow_up',
      'status' => 'active',
    ]);

    // Create Department Leaders and assign to departments
    $departments = Department::all();
    foreach ($departments->take(5) as $index => $department) {
      $leader = User::create([
        'name' => "Leader of {$department->name}",
        'phone' => '+23320000100' . $index,
        'email' => strtolower(str_replace(' ', '', $department->name)) . '.leader@pleapp.com',
        'password' => Hash::make('password'),
        'role' => 'department_leader',
        'department_id' => $department->id,
        'can_approve_attendance' => true,
        'status' => 'active',
      ]);

      // Update department with leader
      $department->update(['leader_id' => $leader->id]);
    }

    // Create additional members
    User::factory(30)->create([
      'role' => 'usher',
      'department_id' => $departments->random()->id,
    ]);
  }
}
