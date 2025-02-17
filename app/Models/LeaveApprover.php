<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveApprover extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'approver_id',
        'level',
        'is_active',
        'can_approve_all_departments',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'can_approve_all_departments' => 'boolean'
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where(function($q) use ($departmentId) {
            $q->where('department_id', $departmentId)
                ->orWhere('can_approve_all_departments', true);
        });
    }

}
