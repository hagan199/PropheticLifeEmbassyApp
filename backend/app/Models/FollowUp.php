<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'visitor_id',
        'contact_method',
        'outcome_notes',
        'status_after',
        'next_follow_up_date',
        'logged_by',
    ];

    protected $casts = [
        'next_follow_up_date' => 'date',
    ];

    /**
     * Get the visitor this follow-up belongs to
     */
    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    /**
     * Get the user who logged this follow-up
     */
    public function loggedBy()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
