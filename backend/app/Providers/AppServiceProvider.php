<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Permission;
use App\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register authorization gates
        $this->registerPermissionGates();
    }

    /**
     * Register all permission-based gates
     */
    private function registerPermissionGates(): void
    {
        // Admin bypass - admins have all permissions
        Gate::before(function ($user, $ability) {
            if ($user && $user->role === 'admin') {
                return true;
            }
        });

        // Define gates from database permissions
        try {
            $permissions = Permission::all();

            foreach ($permissions as $permission) {
                Gate::define($permission->name, function ($user) use ($permission) {
                    // Get user's role from database
                    $role = Role::where('name', $user->role)->first();

                    if (!$role) {
                        return false;
                    }

                    return $role->hasPermission($permission->name);
                });
            }

            // Define legacy gate names for backward compatibility (BroadcastController)
            Gate::define('view-broadcasts', fn($user) => Gate::allows('broadcasts.view'));
            Gate::define('create-broadcasts', fn($user) => Gate::allows('broadcasts.create'));
            Gate::define('delete-broadcasts', fn($user) => Gate::allows('broadcasts.delete'));

        } catch (\Exception $e) {
            // Silent fail during migrations when tables don't exist yet
            \Log::debug('Permission gates not registered: ' . $e->getMessage());
        }
    }
}
