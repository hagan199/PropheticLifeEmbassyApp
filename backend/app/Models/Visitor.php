<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'category',
        'source',
        'source_detail',
        'ministry_interest',
        'initial_notes',
        'first_visit_date',
        'status',
        'next_follow_up_date',
        'created_by',
    ];

    protected $casts = [
        'ministry_interest' => 'array',
        'first_visit_date' => 'date',
        'next_follow_up_date' => 'date',
    ];

    /**
     * Get the user who created this visitor record
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all follow-ups for this visitor
     */
    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    /**
     * Scope: Get visitors due for follow-up
     */
    public function scopeDueForFollowUp($query)
    {
        return $query->whereNotNull('next_follow_up_date')
                     ->where('next_follow_up_date', '<=', now())
                     ->whereIn('status', ['not_contacted', 'contacted', 'engaged']);
    }

    /**
     * Scope: Get overdue follow-ups
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('next_follow_up_date')
                     ->where('next_follow_up_date', '<', now()->toDateString())
                     ->whereIn('status', ['not_contacted', 'contacted', 'engaged']);
    }
}
