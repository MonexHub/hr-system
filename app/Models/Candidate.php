<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Candidate extends Model
{
    use SoftDeletes, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'photo_path',
        'status',
        'resume_path',
        'skills',
        'education',
        'expected_salary',
        'salary_currency',
        'preferred_language',
        'nationality',
        'current_job_title',
        'years_of_experience',
        'availability_status',
        'notice_period_days',
        'professional_summary'
    ];

    protected $casts = [
        'skills' => 'array',
        'education' => 'array',
        'experience' => 'array',
        'expected_salary' => 'decimal:2'
    ];

    const STATUS_APPLIED = 'applied';
    const STATUS_SCREENING = 'screening';
    const STATUS_SHORTLISTED = 'shortlisted';
    const STATUS_INTERVIEW = 'interview';
    const STATUS_OFFER = 'offer';
    const STATUS_HIRED = 'hired';
    const STATUS_REJECTED = 'rejected';
    const STATUS_WITHDRAWN = 'withdrawn';



    public static function getStatuses(): array
    {
        return [
            self::STATUS_APPLIED => 'Applied',
            self::STATUS_SCREENING => 'In Screening',
            self::STATUS_SHORTLISTED => 'Shortlisted',
            self::STATUS_INTERVIEW => 'Interview Stage',
            self::STATUS_OFFER => 'Offer Stage',
            self::STATUS_HIRED => 'Hired',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_WITHDRAWN => 'Withdrawn',
        ];
    }


    // Helper method for years of experience options
    public static function getYearsOfExperienceOptions(): array
    {
        return [
            '0-1' => 'Less than 1 year',
            '1-2' => '1-2 years',
            '2-3' => '2-3 years',
            '3-5' => '3-5 years',
            '5-7' => '5-7 years',
            '7-10' => '7-10 years',
            '10-15' => '10-15 years',
            '15+' => 'More than 15 years'
        ];
    }

    // Helper method for nationality options
    public static function getNationalityOptions(): array
    {
        return [
            'TZ' => 'Tanzanian',
            'KE' => 'Kenyan',
            'UG' => 'Ugandan',
            'RW' => 'Rwandan',
            'BI' => 'Burundian',
            'CD' => 'Congolese',
            'MZ' => 'Mozambican',
            'ZM' => 'Zambian',
            'MW' => 'Malawian',
            'other' => 'Other'
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    // Helper Methods
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
