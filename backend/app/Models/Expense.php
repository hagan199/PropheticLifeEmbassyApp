<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'expense_type_id',
        'category',
        'amount',
        'expense_date',
        'description',
        'receipt_path',
        'status',
        'rejection_reason',
        'notes',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the expense type
     */
    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    /**
     * Get the user who submitted the expense
     */
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who approved the expense
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: Get approved expenses only
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Get pending expenses
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending_approval');
    }

    /**
     * Scope: Get expenses for current month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('expense_date', now()->year)
                     ->whereMonth('expense_date', now()->month);
    }
}
