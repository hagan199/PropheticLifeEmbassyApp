<?php


namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleAndPermissionSeeder extends Seeder
{
  public function run(): void
  {
    // Truncate tables with CASCADE for PostgreSQL
    \DB::statement('TRUNCATE TABLE role_permissions, roles, permissions RESTART IDENTITY CASCADE');
    // Create Roles
    $roles = [
      ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Full system access', 'is_system' => true],
      ['name' => 'pastor', 'display_name' => 'Pastor', 'description' => 'Pastoral oversight access', 'is_system' => true],
      ['name' => 'finance', 'display_name' => 'Finance Officer', 'description' => 'Financial management access', 'is_system' => true],
      ['name' => 'usher', 'display_name' => 'Usher', 'description' => 'Attendance management access', 'is_system' => true],
      ['name' => 'pr_follow_up', 'display_name' => 'PR/Follow-up', 'description' => 'Visitor and follow-up management', 'is_system' => true],
      ['name' => 'department_leader', 'display_name' => 'Department Leader', 'description' => 'Department management access', 'is_system' => true],
    ];

    foreach ($roles as $role) {
      Role::updateOrCreate(['name' => $role['name']], $role);
    }

    // Create Permissions
    $permissions = [
      // User Management
      ['name' => 'users.view', 'display_name' => 'View Users', 'module' => 'users'],
      ['name' => 'users.create', 'display_name' => 'Create Users', 'module' => 'users'],
      ['name' => 'users.edit', 'display_name' => 'Edit Users', 'module' => 'users'],
      ['name' => 'users.delete', 'display_name' => 'Delete Users', 'module' => 'users'],

      // Attendance
      ['name' => 'attendance.view', 'display_name' => 'View Attendance', 'module' => 'attendance'],
      ['name' => 'attendance.create', 'display_name' => 'Record Attendance', 'module' => 'attendance'],
      ['name' => 'attendance.approve', 'display_name' => 'Approve Attendance', 'module' => 'attendance'],
      ['name' => 'attendance.reject', 'display_name' => 'Reject Attendance', 'module' => 'attendance'],

      // Contributions
      ['name' => 'contributions.view', 'display_name' => 'View Contributions', 'module' => 'contributions'],
      ['name' => 'contributions.create', 'display_name' => 'Record Contributions', 'module' => 'contributions'],
      ['name' => 'contributions.approve', 'display_name' => 'Approve Contributions', 'module' => 'contributions'],
      ['name' => 'contributions.export', 'display_name' => 'Export Contributions', 'module' => 'contributions'],

      // Expenses
      ['name' => 'expenses.view', 'display_name' => 'View Expenses', 'module' => 'expenses'],
      ['name' => 'expenses.create', 'display_name' => 'Create Expenses', 'module' => 'expenses'],
      ['name' => 'expenses.approve', 'display_name' => 'Approve Expenses', 'module' => 'expenses'],

      // Visitors
      ['name' => 'visitors.view', 'display_name' => 'View Visitors', 'module' => 'visitors'],
      ['name' => 'visitors.create', 'display_name' => 'Register Visitors', 'module' => 'visitors'],
      ['name' => 'visitors.follow_up', 'display_name' => 'Log Follow-ups', 'module' => 'visitors'],

      // Broadcasts
      ['name' => 'broadcasts.view', 'display_name' => 'View Broadcasts', 'module' => 'broadcasts'],
      ['name' => 'broadcasts.create', 'display_name' => 'Send Broadcasts', 'module' => 'broadcasts'],

      // Reports
      ['name' => 'reports.view', 'display_name' => 'View Reports', 'module' => 'reports'],
      ['name' => 'reports.export', 'display_name' => 'Export Reports', 'module' => 'reports'],

      // Audit
      ['name' => 'audit.view', 'display_name' => 'View Audit Logs', 'module' => 'audit'],
    ];

    foreach ($permissions as $permission) {
      Permission::create($permission);
    }

    // Assign permissions to roles
    $adminRole = Role::where('name', 'admin')->first();
    $adminRole->permissions()->attach(Permission::all());

    $pastorRole = Role::where('name', 'pastor')->first();
    $pastorRole->permissions()->attach(
      Permission::whereIn('name', [
        'users.view',
        'attendance.view',
        'attendance.approve',
        'attendance.reject',
        'contributions.view',
        'expenses.view',
        'visitors.view',
        'broadcasts.view',
        'broadcasts.create',
        'reports.view',
        'reports.export'
      ])->get()
    );

    $financeRole = Role::where('name', 'finance')->first();
    $financeRole->permissions()->attach(
      Permission::whereIn('name', [
        'contributions.view',
        'contributions.create',
        'contributions.approve',
        'contributions.export',
        'expenses.view',
        'expenses.create',
        'expenses.approve',
        'reports.view',
        'reports.export'
      ])->get()
    );

    $usherRole = Role::where('name', 'usher')->first();
    $usherRole->permissions()->attach(
      Permission::whereIn('name', [
        'attendance.view',
        'attendance.create',
        'visitors.view',
        'visitors.create'
      ])->get()
    );

    $prRole = Role::where('name', 'pr_follow_up')->first();
    $prRole->permissions()->attach(
      Permission::whereIn('name', [
        'visitors.view',
        'visitors.create',
        'visitors.follow_up',
        'broadcasts.view',
        'broadcasts.create'
      ])->get()
    );

    $deptLeaderRole = Role::where('name', 'department_leader')->first();
    $deptLeaderRole->permissions()->attach(
      Permission::whereIn('name', [
        'users.view',
        'attendance.view',
        'attendance.create',
        'attendance.approve'
      ])->get()
    );
  }
}
