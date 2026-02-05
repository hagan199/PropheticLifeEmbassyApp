<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all expenses of this type
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'expense_type_id');
    }

    /**
     * Scope: Get active expense types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
