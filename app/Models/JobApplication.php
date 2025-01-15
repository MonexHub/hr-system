<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_number',
        'job_posting_id',
        'candidate_id',
        'status',
        'cover_letter_path',
        'additional_documents',
        'screening_answers',
        'rejection_reason',
        'interview_feedback',
        'assessment_results',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'additional_documents' => 'array',
        'screening_answers' => 'array',
        'interview_feedback' => 'array',
        'assessment_results' => 'array',
        'reviewed_at' => 'datetime'
    ];

    // Relationships
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(InterviewSchedule::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(JobOffer::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeInReview($query)
    {
        return $query->whereIn('status', ['under_review', 'shortlisted']);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['rejected', 'withdrawn', 'hired']);
    }

    // Helpers
    public function getLatestOfferAttribute()
    {
        return $this->offers()->latest()->first();
    }

    public function getNextInterviewAttribute()
    {
        return $this->interviews()
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->first();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->application_number)) {
                $lastApplication = self::withTrashed()->latest('id')->first();
                $nextNumber = $lastApplication ? $lastApplication->id + 1 : 1;
                $model->application_number = 'APP-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
