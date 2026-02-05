<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'attendance';

    protected $fillable = [
        'member_id',
        'service_type',
        'service_date',
        'count',
        'status',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
        'resubmitted_from',
    ];

    protected $casts = [
        'service_date' => 'date',
        'count' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

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
     * Get the original attendance record if this is a resubmission
     */
    public function originalRecord()
    {
        return $this->belongsTo(Attendance::class, 'resubmitted_from');
    }

    /**
     * Scope: Get approved records only
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Get pending records
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
