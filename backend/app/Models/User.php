<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'department_id',
        'has_2fa',
        'can_approve_attendance',
        'status',
        'avatar',
        'deactivated_at',
        'deactivation_reason',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_2fa' => 'boolean',
            'can_approve_attendance' => 'boolean',
            'deactivated_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the role name for display
     */
    public function getRoleNameAttribute(): string
    {
        $roles = [
            'admin' => 'Administrator',
            'pastor' => 'Pastor',
            'usher' => 'Usher',
            'finance' => 'Finance Officer',
            'pr_follow_up' => 'PR/Follow-up',
            'department_leader' => 'Department Leader',
        ];

        return $roles[$this->role] ?? $this->role;
    }

    /**
     * Relationship: Department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relationship: Attendance records submitted by this user
     */
    public function submittedAttendance()
    {
        return $this->hasMany(Attendance::class, 'submitted_by');
    }

    /**
     * Relationship: Attendance records approved by this user
     */
    public function approvedAttendance()
    {
        return $this->hasMany(Attendance::class, 'approved_by');
    }

    /**
     * Relationship: Visitors created by this user
     */
    public function visitors()
    {
        return $this->hasMany(Visitor::class, 'created_by');
    }

    /**
     * Relationship: Follow-ups logged by this user
     */
    public function followUps()
    {
        return $this->hasMany(FollowUp::class, 'logged_by');
    }

    /**
     * Relationship: Contributions recorded by this user
     */
    public function recordedContributions()
    {
        return $this->hasMany(Contribution::class, 'recorded_by');
    }

    /**
     * Relationship: Contributions made by this user (as a member)
     */
    public function contributions()
    {
        return $this->hasMany(Contribution::class, 'member_id');
    }

    /**
     * Relationship: Expenses submitted by this user
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'submitted_by');
    }

    /**
     * Relationship: Broadcasts sent by this user
     */
    public function broadcasts()
    {
        return $this->hasMany(Broadcast::class, 'sender_id');
    }

    /**
     * Relationship: Audit logs for this user
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Relationship: Multiple roles (many-to-many)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * Check if user has a specific role
     * Supports both old single role field and new multiple roles
     */
    public function hasRole($role)
    {
        // Check new many-to-many relationship first
        if ($this->roles()->count() > 0) {
            return $this->roles()->where('name', $role)->exists();
        }
        // Fallback to old single role field
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     * Supports both old single role field and new multiple roles
     */
    public function hasAnyRole(array $roles)
    {
        // Check new many-to-many relationship first
        if ($this->roles()->count() > 0) {
            return $this->roles()->whereIn('name', $roles)->exists();
        }
        // Fallback to old single role field
        return in_array($this->role, $roles);
    }

    /**
     * Get all role names for this user
     */
    public function getRoleNames()
    {
        if ($this->roles()->count() > 0) {
            return $this->roles()->pluck('name')->toArray();
        }
        // Fallback to old single role field
        return $this->role ? [$this->role] : [];
    }

    /**
     * Scope: Active users only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->whereNull('deactivated_at');
    }

    /**
     * Scope: Filter by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope: Filter by department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}
