<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'recipient_group',
        'department_id',
        'channel',
        'message',
        'total_recipients',
        'delivered_count',
        'failed_count',
        'delivery_rate',
        'status',
        'scheduled_for',
        'sent_at',
        'error_reason',
        'retry_count',
        'sender_id',
    ];

    protected $casts = [
        'total_recipients' => 'integer',
        'delivered_count' => 'integer',
        'failed_count' => 'integer',
        'delivery_rate' => 'decimal:2',
        'retry_count' => 'integer',
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the sender of this broadcast
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the department if broadcast was sent to a department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Scope: Get sent broadcasts
     */
    public function scopeSent($query)
    {
        return $query->whereIn('status', ['sent', 'partially_sent']);
    }

    /**
     * Scope: Get scheduled broadcasts
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                     ->whereNotNull('scheduled_for');
    }

    /**
     * Scope: Get broadcasts due to be sent
     */
    public function scopeDueToSend($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_for', '<=', now());
    }

    /**
     * Calculate and update delivery rate
     */
    public function updateDeliveryRate()
    {
        if ($this->total_recipients > 0) {
            $this->delivery_rate = ($this->delivered_count / $this->total_recipients) * 100;
            $this->save();
        }
    }
}
