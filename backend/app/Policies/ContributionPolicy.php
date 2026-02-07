<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Contribution;
use Illuminate\Support\Facades\Gate;

class ContributionPolicy
{
    /**
     * Determine if user can view any contributions
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('contributions.view');
    }

    /**
     * Determine if user can view a specific contribution
     */
    public function view(User $user, Contribution $contribution): bool
    {
        return Gate::allows('contributions.view');
    }

    /**
     * Determine if user can create contributions
     */
    public function create(User $user): bool
    {
        return Gate::allows('contributions.create');
    }

    /**
     * Determine if user can update a contribution
     */
    public function update(User $user, Contribution $contribution): bool
    {
        // Only pending contributions can be updated
        return $contribution->status === 'pending' && Gate::allows('contributions.create');
    }

    /**
     * Determine if user can delete a contribution
     */
    public function delete(User $user, Contribution $contribution): bool
    {
        return $user->role === 'admin'; // Only admin can delete
    }

    /**
     * Determine if user can approve contributions
     */
    public function approve(User $user): bool
    {
        return Gate::allows('contributions.approve');
    }

    /**
     * Determine if user can export contributions
     */
    public function export(User $user): bool
    {
        return Gate::allows('contributions.export');
    }
}
