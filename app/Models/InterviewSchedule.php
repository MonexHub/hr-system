<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_application_id',
        'round_number',
        'interviewer_id',
        'scheduled_at',
        'duration_minutes',
        'location',
        'meeting_link',
        'type',
        'mode',
        'status',
        'cancellation_reason',
        'interview_questions',
        'feedback',
        'rating',
        'notes',
        'recommendations',
        'created_by'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'interview_questions' => 'array',
        'feedback' => 'array',
        'rating' => 'integer'
    ];

    // Relationships
    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->where('status', 'scheduled');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today())
            ->whereIn('status', ['scheduled', 'confirmed']);
    }

    // Helpers
    public function getDurationInHoursAttribute(): float
    {
        return $this->duration_minutes / 60;
    }

    public function isUpcoming(): bool
    {
        return $this->scheduled_at > now() &&
            in_array($this->status, ['scheduled', 'confirmed']);
    }

    public function canBeRescheduled(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']) &&
            $this->scheduled_at > now();
    }
}
