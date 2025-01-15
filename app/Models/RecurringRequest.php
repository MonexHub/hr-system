<?php

namespace App\Models;

use App\Traits\HasSupportFeatures;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Notification;

class RecurringRequest extends Model
{
    use HasFactory, SoftDeletes;
    use HasSupportFeatures;

    protected $fillable = [
        'employee_id',
        'request_type',      // recruitment, promotion, transfer, training, etc.
        'title',
        'description',
        'priority',         // high, medium, low
        'status',          // draft, submitted, in_review, approved, rejected, completed
        'target_date',
        'completion_date',
        'approved_by',
        'rejection_reason',
        'attachment_path',
        'reference_number', // Auto-generated
        'department_id',
        'category',        // personal, professional, organizational
        'estimated_cost',
        'currency',
        'impact_level',    // individual, team, department, organization
        'skills_required',
        'benefits',
        'risks',
        'alternatives_considered',
        'resource_requirements',
        'success_criteria',
        'evaluation_metrics',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completion_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'skills_required' => 'array',
        'benefits' => 'array',
        'risks' => 'array',
        'alternatives_considered' => 'array',
        'resource_requirements' => 'array',
        'success_criteria' => 'array',
        'evaluation_metrics' => 'array',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'loggable');
    }

    // Generate unique reference number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->reference_number = 'RR-' . date('Y') . '-' . str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 5, '0', STR_PAD_LEFT);
        });
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'in_review']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_date', '<', now())->whereNotIn('status', ['completed', 'rejected']);
    }

    // Methods
    public function submit()
    {
        $this->update(['status' => 'submitted']);
        // Trigger notification
    }

    public function approve($userId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId
        ]);
        // Trigger notification
    }

    public function reject($userId, $reason)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'rejection_reason' => $reason
        ]);
        // Trigger notification
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completion_date' => now()
        ]);
        // Trigger notification
    }
}
