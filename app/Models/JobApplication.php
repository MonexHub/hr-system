<?php

namespace App\Models;

use App\Notifications\RecruitmentNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class JobApplication extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'application_number',
        'job_posting_id',
        'candidate_id',
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
        'other_attachments',
        'portfolio_url',
        'linkedin_url',
        'expected_salary',
        'notice_period',
        'referral_source',
        'additional_notes',
        'status',
        'reviewed_by',
        'reviewed_at'
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

    public function scheduleInterview()
    {
        DB::transaction(function () {
            $this->update(['status' => 'interview_scheduled']);
            if ($this->candidate) {
                $this->candidate->update(['status' => 'interview']);
            }
        });
    }

    public function hire()
    {
        DB::transaction(function () {
            $this->update(['status' => 'hired']);
            if ($this->candidate) {
                $this->candidate->update(['status' => 'hired']);
            }
        });
    }

    public function reject()
    {
        DB::transaction(function () {
            $this->update(['status' => 'rejected']);
            if ($this->candidate) {
                $this->candidate->update(['status' => 'rejected']);
            }
        });
    }


    protected static function boot()
    {
        parent::boot();

        static::created(function ($application) {
            if ($application->candidate) {
                $application->candidate->notify(new RecruitmentNotification('created', [ // Added 'created' type
                    'job_title' => $application->jobPosting->title,
                    'application_number' => $application->application_number,
                    'application_id' => $application->id
                ]));
            }
        });
    }

    public function shortlist()
    {
        DB::transaction(function () {
            $this->update(['status' => 'shortlisted']);
            if ($this->candidate) {
                $this->candidate->update(['status' => 'shortlisted']);
                $this->candidate->notify(new RecruitmentNotification('shortlisted', [
                    'job_title' => $this->jobPosting->title,
                    'application_id' => $this->id
                ]));
            }
        });
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
