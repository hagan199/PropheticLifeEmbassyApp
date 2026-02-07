<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UserPolicy
{
    /**
     * Determine if user can view any users
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('users.view');
    }

    /**
     * Determine if user can view a specific user
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile or if they have permission
        return $user->id === $model->id || Gate::allows('users.view');
    }

    /**
     * Determine if user can create users
     */
    public function create(User $user): bool
    {
        return Gate::allows('users.create');
    }

    /**
     * Determine if user can update a user
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile or if they have permission
        return $user->id === $model->id || Gate::allows('users.edit');
    }

    /**
     * Determine if user can delete a user
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        return Gate::allows('users.delete');
    }

    /**
     * Determine if user can deactivate a user
     */
    public function deactivate(User $user, User $model): bool
    {
        // Cannot deactivate yourself
        if ($user->id === $model->id) {
            return false;
        }

        return Gate::allows('users.edit');
    }

    /**
     * Determine if user can reactivate a user
     */
    public function reactivate(User $user): bool
    {
        return Gate::allows('users.edit');
    }
}
