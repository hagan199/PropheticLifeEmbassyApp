<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;

class PermissionCacheService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_KEY = 'permissions.role.';

    /**
     * Get all permissions for a role (cached)
     *
     * @param string $roleName
     * @return array
     */
    public static function getRolePermissions(string $roleName): array
    {
        return Cache::remember(
            self::CACHE_KEY . $roleName,
            self::CACHE_TTL,
            function () use ($roleName) {
                $role = Role::where('name', $roleName)->first();
                return $role ? $role->permissions()->pluck('name')->toArray() : [];
            }
        );
    }

    /**
     * Check if role has a specific permission (cached)
     *
     * @param string $roleName
     * @param string $permissionName
     * @return bool
     */
    public static function hasPermission(string $roleName, string $permissionName): bool
    {
        $permissions = self::getRolePermissions($roleName);
        return in_array($permissionName, $permissions);
    }

    /**
     * Clear cache for a specific role or all roles
     *
     * @param string|null $roleName
     * @return void
     */
    public static function clearCache(?string $roleName = null): void
    {
        if ($roleName) {
            Cache::forget(self::CACHE_KEY . $roleName);
        } else {
            // Clear all role permission caches
            $roles = Role::pluck('name');
            foreach ($roles as $role) {
                Cache::forget(self::CACHE_KEY . $role);
            }
        }
    }

    /**
     * Warm up cache for all roles
     *
     * @return void
     */
    public static function warmCache(): void
    {
        $roles = Role::all();
        foreach ($roles as $role) {
            self::getRolePermissions($role->name);
        }
    }
}
