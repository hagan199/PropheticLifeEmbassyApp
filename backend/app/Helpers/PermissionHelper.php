<?php

namespace App\Helpers;

class PermissionHelper
{
    /**
     * Get permissions for a role
     *
     * @param string $role
     * @return array
     */
    public static function getPermissions(string $role): array
    {
        return match ($role) {
            'admin' => [
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
                'attendance.view',
                'attendance.approve',
                'attendance.reject',
                'visitors.view',
                'visitors.create',
                'visitors.update',
                'visitors.delete',
                'followups.view',
                'followups.create',
                'contributions.view',
                'contributions.create',
                'expenses.view',
                'expenses.approve',
                'expenses.reject',
                'departments.view',
                'departments.create',
                'departments.update',
                'departments.delete',
                'broadcasts.send',
                'audit.view',
                'settings.manage',
            ],
            'pastor' => [
                'attendance.view',
                'members.view',
                'departments.view',
                'reports.view',
            ],
            'usher' => [
                'attendance.record',
                'attendance.view_own',
                'members.search',
            ],
            'finance' => [
                'contributions.view',
                'contributions.create',
                'contributions.update',
                'expenses.view',
                'expenses.create',
                'expenses.approve',
                'expenses.reject',
                'reports.view',
                'reports.export',
            ],
            'pr_follow_up' => [
                'visitors.view',
                'visitors.create',
                'visitors.update',
                'followups.view',
                'followups.create',
                'followups.update',
                'broadcasts.send',
            ],
            'department_leader' => [
                'department.view_own',
                'department.manage_members',
                'members.view',
                'attendance.view_department',
            ],
            default => [],
        };
    }

    /**
     * Check if a role has a specific permission
     *
     * @param string $role
     * @param string $permission
     * @return bool
     */
    public static function hasPermission(string $role, string $permission): bool
    {
        $permissions = self::getPermissions($role);
        return in_array($permission, $permissions);
    }

    /**
     * Check if a role has any of the given permissions
     *
     * @param string $role
     * @param array $permissions
     * @return bool
     */
    public static function hasAnyPermission(string $role, array $permissions): bool
    {
        $rolePermissions = self::getPermissions($role);

        foreach ($permissions as $permission) {
            if (in_array($permission, $rolePermissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a role has all of the given permissions
     *
     * @param string $role
     * @param array $permissions
     * @return bool
     */
    public static function hasAllPermissions(string $role, array $permissions): bool
    {
        $rolePermissions = self::getPermissions($role);

        foreach ($permissions as $permission) {
            if (!in_array($permission, $rolePermissions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all available roles
     *
     * @return array
     */
    public static function getRoles(): array
    {
        return [
            'admin' => 'Administrator',
            'pastor' => 'Pastor',
            'usher' => 'Usher',
            'finance' => 'Finance Officer',
            'pr_follow_up' => 'PR/Follow-up',
            'department_leader' => 'Department Leader',
        ];
    }

    /**
     * Get role display name
     *
     * @param string $role
     * @return string
     */
    public static function getRoleName(string $role): string
    {
        $roles = self::getRoles();
        return $roles[$role] ?? $role;
    }
}
