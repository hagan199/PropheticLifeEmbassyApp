<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Gate;

class AttendancePolicy
{
    /**
     * Determine if user can view any attendance records
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('attendance.view');
    }

    /**
     * Determine if user can view a specific attendance record
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // User can view their own submissions or if they have permission
        return $user->id === $attendance->submitted_by || Gate::allows('attendance.view');
    }

    /**
     * Determine if user can create attendance records
     */
    public function create(User $user): bool
    {
        return Gate::allows('attendance.create');
    }

    /**
     * Determine if user can update attendance record
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // Only allow update of own pending submissions
        return $user->id === $attendance->submitted_by &&
               $attendance->status === 'pending';
    }

    /**
     * Determine if user can delete attendance record
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        // Admin or own pending submissions
        return $user->role === 'admin' ||
               ($user->id === $attendance->submitted_by && $attendance->status === 'pending');
    }

    /**
     * Determine if user can approve attendance records
     */
    public function approve(User $user): bool
    {
        return Gate::allows('attendance.approve');
    }

    /**
     * Determine if user can reject attendance records
     */
    public function reject(User $user): bool
    {
        return Gate::allows('attendance.reject');
    }
}
