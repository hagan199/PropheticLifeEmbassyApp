<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Facades\Gate;

class VisitorPolicy
{
    /**
     * Determine if user can view any visitors
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('visitors.view');
    }

    /**
     * Determine if user can view a specific visitor
     */
    public function view(User $user, Visitor $visitor): bool
    {
        return Gate::allows('visitors.view');
    }

    /**
     * Determine if user can create visitors
     */
    public function create(User $user): bool
    {
        return Gate::allows('visitors.create');
    }

    /**
     * Determine if user can update a visitor
     */
    public function update(User $user, Visitor $visitor): bool
    {
        return Gate::allows('visitors.create'); // Same as create permission
    }

    /**
     * Determine if user can delete a visitor
     */
    public function delete(User $user, Visitor $visitor): bool
    {
        return $user->role === 'admin'; // Only admin can delete
    }

    /**
     * Determine if user can add follow-ups
     */
    public function followUp(User $user): bool
    {
        return Gate::allows('visitors.follow_up');
    }
}
