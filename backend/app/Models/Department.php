<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'leader_id',
        'member_count',
    ];

    protected $casts = [
        'member_count' => 'integer',
    ];

    /**
     * Get the leader of the department
     */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Get all members in this department
     */
    public function members()
    {
        return $this->hasMany(User::class, 'department_id');
    }
}
