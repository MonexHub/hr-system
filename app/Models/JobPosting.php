<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class JobPosting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'position_code',
        'title',
        'description',
        'document_path',
        'is_document_based',
        'requirements',
        'responsibilities',
        'employment_type',
        'location',
        'is_remote',
        'salary_min',
        'salary_max',
        'salary_currency',
        'hide_salary',
        'positions_available',
        'positions_filled',
        'publishing_date',
        'closing_date',
        'status',
        'is_featured',
        'skills_required',
        'education_requirements',
        'experience_requirements',
        'benefits',
        'screening_questions',
        'minimum_years_experience',
        'education_level',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'requirements' => 'array',
        'responsibilities' => 'array',
        'is_remote' => 'boolean',
        'hide_salary' => 'boolean',
        'is_featured' => 'boolean',
        'is_document_based' => 'boolean',
        'skills_required' => 'array',
        'education_requirements' => 'array',
        'experience_requirements' => 'array',
        'benefits' => 'array',
        'screening_questions' => 'array',
        'publishing_date' => 'date',
        'closing_date' => 'date',
        'approved_at' => 'datetime',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('closing_date')
                    ->orWhere('closing_date', '>=', now());
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->published();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['published', 'pending_approval']);
    }

    // Methods
    public function isOpen(): bool
    {
        return $this->status === 'published' &&
            ($this->closing_date === null || $this->closing_date >= now()) &&
            $this->positions_filled < $this->positions_available;
    }

    public function approve(int $approver_id)
    {
        $this->update([
            'status' => 'published',
            'approved_by' => $approver_id,
            'approved_at' => now(),
            'publishing_date' => $this->publishing_date ?? now()
        ]);
    }

    public function reject()
    {
        $this->update(['status' => 'draft']);
    }

    public function close()
    {
        $this->update(['status' => 'closed']);
    }

    public function markAsFilled()
    {
        $this->update([
            'status' => 'filled',
            'closing_date' => now()
        ]);
    }

    // Format salary range for display
    public function getSalaryRangeAttribute(): string
    {
        if ($this->hide_salary) {
            return 'Not Disclosed';
        }

        if ($this->salary_min && $this->salary_max) {
            return "{$this->salary_currency} {$this->salary_min} - {$this->salary_max}";
        }

        if ($this->salary_min) {
            return "From {$this->salary_currency} {$this->salary_min}";
        }

        if ($this->salary_max) {
            return "Up to {$this->salary_currency} {$this->salary_max}";
        }

        return 'Negotiable';
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jobPosting) {
            // Generate position code
            if (empty($jobPosting->position_code)) {
                $jobPosting->position_code = 'JOB-' . date('Y') . '-' .
                    str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 5, '0', STR_PAD_LEFT);
            }

            // Set created_by
            if (empty($jobPosting->created_by)) {
                $jobPosting->created_by = auth()->id();
            }
        });

        static::updating(function ($jobPosting) {
            if ($jobPosting->isDirty('status') && $jobPosting->status === 'filled') {
                $jobPosting->closing_date = now();
            }
        });

        static::deleting(function ($jobPosting) {
            // Clean up uploaded document if exists
            if ($jobPosting->document_path) {
                Storage::delete($jobPosting->document_path);
            }
        });
    }

    public function getApplicationsCountAttribute(): int
    {
        return $this->applications()->count();
    }

    public function getShortlistedCountAttribute(): int
    {
        return $this->applications()
            ->where('status', 'shortlisted')
            ->count();
    }

    public function getInterviewedCountAttribute(): int
    {
        return $this->applications()
            ->whereIn('status', ['interview_scheduled', 'interview_completed'])
            ->count();
    }

    public function getHiredCountAttribute(): int
    {
        return $this->applications()
            ->where('status', 'hired')
            ->count();
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

}
