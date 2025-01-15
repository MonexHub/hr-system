<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    use HasFactory, SoftDeletes;

    use SoftDeletes;

    protected $fillable = [
        'department_id',
        'position_code',
        'title',
        'description',
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
        'additional_requirements',
        'created_by',
        'approved_by',
        'approved_at',
        'requirements',
        'skills_required',
        'education_requirements',
        'experience_requirements',
        'benefits',
        'screening_questions',

    ];

    protected $casts = [
        'requirements' => 'array',
        'responsibilities' => 'array',
        'is_remote' => 'boolean',
        'hide_salary' => 'boolean',
        'is_featured' => 'boolean',
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

    public function scopeClosingSoon($query, $days = 7)
    {
        return $query->published()
            ->whereNotNull('closing_date')
            ->where('closing_date', '<=', now()->addDays($days));
    }

    // Methods
    public function hasApplied($employee): bool
    {
        if (!$employee) return false;
        return $this->applications()->where('employee_id', $employee->id)->exists();
    }

    public function isOpen(): bool
    {
        return $this->status === 'published' &&
            ($this->closing_date === null || $this->closing_date >= now()) &&
            $this->positions_filled < $this->positions_available;
    }

    public function publish()
    {
        $this->update([
            'status' => 'published',
            'publishing_date' => now()
        ]);
    }

    public function close()
    {
        $this->update(['status' => 'closed']);
    }

    public function approve(int $approver_id)
    {
        $this->update([
            'status' => 'published',
            'approved_by' => $approver_id,
            'approved_at' => now(),
            'publishing_date' => now()
        ]);
    }

    public function reject()
    {
        $this->update(['status' => 'draft']);
    }

    // Accessor for formatted salary range
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

    // Boot method for automatic position code generation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jobPosting) {
            if (empty($jobPosting->position_code)) {
                $jobPosting->position_code = 'JOB-' . date('Y') . '-' .
                    str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });

        static::updating(function ($jobPosting) {
            if ($jobPosting->isDirty('status') && $jobPosting->status === 'filled') {
                $jobPosting->closing_date = now();
            }
        });

        static::creating(function ($jobPosting) {
            // Automatically set the created_by to the currently authenticated user
            if (empty($jobPosting->created_by)) {
                $jobPosting->created_by = auth()->id();
            }
        });
    }

    protected static function booted(): void
    {
        static::creating(function ($jobPosting) {
            $jobPosting->position_code = 'JOB-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $jobPosting->created_by = auth()->id();
        });
    }
}
