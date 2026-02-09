<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class MigrateUserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Migrate existing users with single role field to many-to-many relationship
     */
    public function run(): void
    {
        $this->command->info('Migrating existing user roles to many-to-many relationship...');

        // Get all users who have a role field set
        $users = User::whereNotNull('role')->get();

        $migratedCount = 0;
        $skippedCount = 0;

        foreach ($users as $user) {
            // Check if user already has roles in pivot table
            if ($user->roles()->count() > 0) {
                $this->command->warn("User {$user->name} already has roles in pivot table. Skipping.");
                $skippedCount++;
                continue;
            }

            // Find the role by name (roles table uses 'name' field)
            $role = Role::where('name', $user->role)->first();

            if (!$role) {
                $this->command->warn("Role '{$user->role}' not found for user {$user->name}. Skipping.");
                $skippedCount++;
                continue;
            }

            // Attach the role to the user
            $user->roles()->attach($role->id);

            $this->command->info("✓ Migrated role '{$role->name}' for user {$user->name}");
            $migratedCount++;
        }

        $this->command->info("Migration complete!");
        $this->command->info("Migrated: {$migratedCount} users");
        $this->command->info("Skipped: {$skippedCount} users");
    }
}
