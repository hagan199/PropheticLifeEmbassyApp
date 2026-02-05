<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinisterAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'attendance';

    protected $fillable = [
        'member_id',
        'service_type',
        'service_date',
        'service_time',
        'unit',
        'member_name',
        'present',
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
        'service_time' => 'string',
        'present' => 'boolean',
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
    }