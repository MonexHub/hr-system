<?php

namespace App\Models;

use App\Notifications\AppraisalSubmittedNotification;
use App\Traits\PerformanceCalculations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class PerformanceAppraisal extends Model
{
    use SoftDeletes , PerformanceCalculations;

    // Simplified Status Constants
    const STATUS_DRAFT = 'draft';                // Initial state
    const STATUS_SUBMITTED = 'submitted';        // Submitted by employee
    const STATUS_SUPERVISOR_APPROVED = 'supervisor_approved'; // Approved by HOD/Supervisor
    const STATUS_HR_APPROVED = 'hr_approved';    // Final HR approval
    const STATUS_COMPLETED = 'completed';        // Process completed

    protected $fillable = [
        'employee_id',
        'immediate_supervisor_id',
        'evaluation_date',
        'evaluation_period_start',
        'evaluation_period_end',
        'quality_of_work',
        'productivity',
        'job_knowledge',
        'reliability',
        'communication',
        'teamwork',
        'achievements',
        'areas_for_improvement',
        'supervisor_comments',
        'hr_comments',
        'overall_rating',
        'status',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'evaluation_period_start' => 'date',
        'evaluation_period_end' => 'date',
        'overall_rating' => 'decimal:2',
    ];



    // Relationships
    public function objectives()
    {
        return $this->hasMany(PerformanceObjective::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'immediate_supervisor_id');
    }

    public function hr(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'hr_id');
    }

    // Status transition methods
    public function submit(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $updated = $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        if ($updated && $this->supervisor?->user) {
            $this->supervisor->user->notify(
                new AppraisalSubmittedNotification($this, 'submitted')
            );
        }

        return $updated;
    }

    public function supervisorApprove(): bool
    {
        if ($this->status !== self::STATUS_SUBMITTED) {
            return false;
        }

        $updated = $this->update([
            'status' => self::STATUS_SUPERVISOR_APPROVED,
            'supervisor_approved_at' => now(),
        ]);

        if ($updated) {
            // Notify HR
            $this->notifyHR();
        }

        return $updated;
    }

    public function hrApprove(): bool
    {
        if ($this->status !== self::STATUS_SUPERVISOR_APPROVED) {
            return false;
        }

        $updated = $this->update([
            'status' => self::STATUS_HR_APPROVED,
            'hr_approved_at' => now(),
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now()
        ]);

        if ($updated) {
            // Notify employee and supervisor of completion
            $this->notifyCompletion();
        }

        return $updated;
    }

    // Helper methods
    protected function notifyHR()
    {
        // Implement HR notification logic
    }

    protected function notifyCompletion(): void
    {
        if ($this->employee?->user) {
            $this->employee->user->notify(
                new AppraisalSubmittedNotification($this, 'completed')
            );
        }

        if ($this->supervisor?->user) {
            $this->supervisor->user->notify(
                new AppraisalSubmittedNotification($this, 'completed')
            );
        }
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SUBMITTED,
            self::STATUS_SUPERVISOR_APPROVED
        ]);
    }

    public function scopeNeedsHRReview($query)
    {
        return $query->where('status', self::STATUS_SUPERVISOR_APPROVED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
