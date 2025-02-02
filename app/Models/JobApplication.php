<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_posting_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'current_position',
        'current_company',
        'experience_years',
        'education_level',
        'resume_path',
        'cover_letter_path',
        'portfolio_url',
        'linkedin_url',
        'other_attachments',
        'status',
        'referral_source',
        'expected_salary',
        'notice_period',
        'interview_availability',
        'additional_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'other_attachments' => 'array',
        'interview_availability' => 'array',
        'reviewed_at' => 'datetime',
        'experience_years' => 'decimal:1',
        'expected_salary' => 'decimal:2',
    ];

    // Relationships
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Methods
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Status methods
    public function markAsReviewed(int $reviewer_id)
    {
        $this->update([
            'status' => 'reviewed',
            'reviewed_by' => $reviewer_id,
            'reviewed_at' => now(),
        ]);
    }

    public function shortlist()
    {
        $this->update(['status' => 'shortlisted']);
    }

    public function reject()
    {
        $this->update(['status' => 'rejected']);
    }

    public function scheduleInterview()
    {
        $this->update(['status' => 'interview_scheduled']);
    }

    public function completeInterview()
    {
        $this->update(['status' => 'interview_completed']);
    }

    public function hire()
    {
        $this->update(['status' => 'hired']);
    }
}
