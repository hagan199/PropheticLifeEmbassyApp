<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinisterUnitAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'minister_unit_attendance';

    protected $fillable = [
        'department_id',
        'unit_name',
        'service_type',
        'service_date',
        'service_time',
        'member_id',
        'member_name',
        'present',
        'status',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'service_date' => 'date',
        'present' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'metadata' => 'json',
    ];

    /**
     * Get the department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the member who attended
     */
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Get the user who submitted the attendance
     */
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who approved the attendance
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: Filter approved attendance
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Filter pending attendance
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Filter rejected attendance
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: Filter by unit name
     */
    public function scopeByUnit($query, $unitName)
    {
        return $query->where('unit_name', $unitName);
    }

    /**
     * Scope: Filter by department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('service_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter by service type
     */
    public function scopeByServiceType($query, $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }
}