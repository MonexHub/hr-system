<?php

namespace App\Models;

use App\Events\DepartmentHeadcountChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_unit_id',
        'name',
        'code',
        'description',
        'parent_id',
        'manager_id',
        'is_active',
        'phone',
        'email',
        'location',
        'annual_budget',
        'current_headcount',
        'max_headcount'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'annual_budget' => 'decimal:2',
        'current_headcount' => 'integer',
        'max_headcount' => 'integer',
    ];

    // Validation Rules
    public static $rules = [
        'max_headcount' => ['required', 'integer', 'min:0'],
        'current_headcount' => ['required', 'integer', 'min:0'],
    ];

    // Headcount Management Methods

    /**
     * Increment the department's headcount
     * @throws ValidationException if exceeds max_headcount
     */
    public function incrementHeadcount(): void
    {
        if (!$this->hasAvailableHeadcount()) {
            throw ValidationException::withMessages([
                'headcount' => "Cannot exceed maximum headcount of {$this->max_headcount} for {$this->name} department"
            ]);
        }

        $this->increment('current_headcount');
    }

    /**
     * Decrement the department's headcount
     */
    public function decrementHeadcount(): void
    {
        if ($this->current_headcount > 0) {
            $this->decrement('current_headcount');
        }
    }

    /**
     * Check if department has available positions
     */
    public function hasAvailableHeadcount(): bool
    {
        return $this->max_headcount === 0 || $this->current_headcount < $this->max_headcount;
    }

    /**
     * Get number of available positions
     */
    public function getAvailablePositionsAttribute(): int
    {
        if ($this->max_headcount === 0) {
            return PHP_INT_MAX; // Unlimited positions
        }
        return max(0, $this->max_headcount - $this->current_headcount);
    }

    /**
     * Get headcount utilization percentage
     */
    public function getHeadcountUtilizationAttribute(): float
    {
        if ($this->max_headcount === 0) {
            return 0;
        }
        return ($this->current_headcount / $this->max_headcount) * 100;
    }

    // Headcount Validation Methods

    /**
     * Validate headcount changes
     * @throws ValidationException
     */
    public function validateHeadcount(int $newHeadcount): void
    {
        if ($this->max_headcount > 0 && $newHeadcount > $this->max_headcount) {
            throw ValidationException::withMessages([
                'headcount' => "New headcount exceeds maximum limit of {$this->max_headcount}"
            ]);
        }

        if ($newHeadcount < 0) {
            throw ValidationException::withMessages([
                'headcount' => 'Headcount cannot be negative'
            ]);
        }
    }

    /**
     * Update max headcount with validation
     * @throws ValidationException
     */
    public function updateMaxHeadcount(int $newMax): void
    {
        if ($newMax < $this->current_headcount) {
            throw ValidationException::withMessages([
                'max_headcount' => 'New maximum headcount cannot be less than current headcount'
            ]);
        }

        $this->update(['max_headcount' => $newMax]);
    }

    // Reporting Methods

    /**
     * Get headcount summary
     */
    public function getHeadcountSummary(): array
    {
        return [
            'department' => $this->name,
            'current_headcount' => $this->current_headcount,
            'max_headcount' => $this->max_headcount,
            'available_positions' => $this->available_positions,
            'utilization_percentage' => round($this->headcount_utilization, 2),
            'is_at_capacity' => !$this->hasAvailableHeadcount(),
        ];
    }

    /**
     * Get detailed employee breakdown
     */
    public function getEmployeeBreakdown(): array
    {
        return [
            'total_employees' => $this->current_headcount,
            'active_employees' => $this->employees()->where('employment_status', 'active')->count(),
            'on_probation' => $this->employees()->where('employment_status', 'probation')->count(),
            'contract_employees' => $this->employees()->where('contract_type', 'contract')->count(),
            'permanent_employees' => $this->employees()->where('contract_type', 'permanent')->count(),
            'by_job_title' => $this->employees()
                ->select('job_title')
                ->selectRaw('count(*) as count')
                ->groupBy('job_title')
                ->pluck('count', 'job_title')
                ->toArray(),
        ];
    }

    /**
     * Get monthly headcount changes
     */
    public function getMonthlyHeadcountChanges(int $months = 12): array
    {
        return $this->employees()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as hired')
            ->whereRaw('created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)', [$months])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    /**
     * Calculate cost per headcount
     */
    public function getCostPerHeadcount(): float
    {
        if ($this->current_headcount === 0) {
            return 0;
        }

        $totalSalaries = $this->employees()->sum('salary');
        return $totalSalaries / $this->current_headcount;
    }

    // Existing relationships...
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($department) {
            if ($department->isDirty('current_headcount')) {
                event(new DepartmentHeadcountChanged($department));
            }
        });
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function departmentHead()
    {
        return $this->hasOne(Employee::class, 'id', 'department_head_id');
    }


}
