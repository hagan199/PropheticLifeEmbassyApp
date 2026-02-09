<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AuditLog;
use App\Models\User;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        $actions = ['create', 'update', 'delete', 'login', 'logout'];
        $entityTypes = ['Users', 'Attendance', 'Visitors', 'Finance', 'Departments', 'Broadcasts'];

        $descriptions = [
            'create' => [
                'Created new user account',
                'Added new attendance record',
                'Registered new visitor',
                'Added financial transaction',
                'Created new department',
                'Published new broadcast'
            ],
            'update' => [
                'Updated user profile',
                'Modified attendance record',
                'Updated visitor information',
                'Edited financial record',
                'Changed department details',
                'Modified broadcast message'
            ],
            'delete' => [
                'Deleted user account',
                'Removed attendance record',
                'Deleted visitor record',
                'Removed financial transaction',
                'Deleted department',
                'Removed broadcast'
            ],
            'login' => [
                'User logged in successfully',
                'Admin access granted',
                'System access authenticated'
            ],
            'logout' => [
                'User logged out',
                'Session terminated',
                'User signed out'
            ]
        ];

        // Create 50 sample audit logs
        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $action = $actions[array_rand($actions)];
            $entityType = $entityTypes[array_rand($entityTypes)];
            $description = $descriptions[$action][array_rand($descriptions[$action])];

            $changes = null;
            if ($action === 'update') {
                $changes = [
                    'old' => ['status' => 'pending', 'count' => rand(50, 100)],
                    'new' => ['status' => 'approved', 'count' => rand(100, 200)]
                ];
            } elseif ($action === 'create') {
                $changes = [
                    'name' => 'Sample Record',
                    'status' => 'active'
                ];
            }

            AuditLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => \Illuminate\Support\Str::uuid(),
                'changes' => $changes,
                'ip_address' => '192.168.1.' . rand(1, 255),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'description' => $description,
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))
            ]);
        }

        $this->command->info('✅ Created 50 sample audit logs');
    }
}
