<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Broadcast;
use Illuminate\Support\Facades\Gate;

class BroadcastPolicy
{
    /**
     * Determine if user can view any broadcasts
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('broadcasts.view');
    }

    /**
     * Determine if user can view a specific broadcast
     */
    public function view(User $user, Broadcast $broadcast): bool
    {
        return Gate::allows('broadcasts.view');
    }

    /**
     * Determine if user can create broadcasts
     */
    public function create(User $user): bool
    {
        return Gate::allows('broadcasts.create');
    }

    /**
     * Determine if user can update a broadcast
     */
    public function update(User $user, Broadcast $broadcast): bool
    {
        // Only unsent broadcasts can be updated
        return $broadcast->status === 'pending' && Gate::allows('broadcasts.create');
    }

    /**
     * Determine if user can delete a broadcast
     */
    public function delete(User $user, Broadcast $broadcast): bool
    {
        return Gate::allows('broadcasts.delete') || $user->role === 'admin';
    }
}
