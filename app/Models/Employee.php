<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_code',
        'application_status',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'marital_status',
        'birthdate',
        'profile_photo',
        'phone_number',
        'email',
        'permanent_address',
        'city',
        'state',
        'postal_code',
        'department_id',
        'job_title_id',
        'branch',
        'employment_status',
        'appointment_date',
        'contract_type',
        'terms_of_employment',
        'contract_end_date',
        'net_salary',
        'salary',
        'reporting_to',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'appointment_date' => 'date',
        'contract_end_date' => 'date',
        'salary' => 'decimal:2',
    ];

    // Self-referencing relationships
    public function reportingTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reporting_to');
    }

    public function reportees(): HasMany
    {
        return $this->hasMany(self::class, 'reporting_to');
    }

    // Direct relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }


    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    public function education(): HasMany
    {
        return $this->hasMany(EmployeeEducation::class);
    }



    public function workExperiences(): HasMany
    {
        return $this->hasMany(EmployeeWorkExperience::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class);
    }

    public function spouse(): HasMany
    {
        return $this->hasMany(Spouse::class);
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(Dependent::class);
    }



    public function companyExperiences(): HasMany
    {
        return $this->hasMany(CompanyExperience::class);
    }

    // Attributes
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(implode(' ', array_filter([
                $this->first_name,
                $this->middle_name,
                $this->last_name,
            ])))
        );
    }

    public function isProfileComplete(): bool
    {
        return $this->application_status === 'active';
    }

    // Model events
    protected static function booted(): void
    {
        static::creating(function ($employee) {
            $employee->employee_code = self::generateEmployeeCode();
        });

        static::created(function ($employee) {
            if (request()->input('create_user_account')) {
                $user = User::create([
                    'name' => $employee->full_name,
                    'email' => $employee->email,
                    'password' => Hash::make(request()->input('password')),
                ]);

                $employee->user()->associate($user);
                $employee->save();

                if (request()->input('roles')) {
                    $user->syncRoles(request()->input('roles'));
                }
            }
        });
    }

    public static function generateEmployeeCode(): string
    {
        return 'EMP-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('application_status', 'active');
    }

    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Additional Helpers
    public function isOnProbation(): bool
    {
        return $this->employment_status === 'probation';
    }

    public function yearsOfService(): int
    {
        return $this->appointment_date->diffInYears(now());
    }

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class, 'unit_id');
    }

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function financials()
    {
        return $this->hasMany(EmployeeFinancial::class);
    }


    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function unit()
    {
        return $this->belongsTo(OrganizationUnit::class, 'unit_id');
    }



    public function performanceAppraisals()
    {
        return $this->hasMany(PerformanceAppraisal::class, 'employee_id');
    }

    public function supervisedAppraisals()
    {
        return $this->hasMany(PerformanceAppraisal::class, 'supervisor_id');
    }

// Helper methods for appraisals
    public function getLatestAppraisal()
    {
        return $this->performanceAppraisals()
            ->latest('evaluation_date')
            ->first();
    }

    public function getPendingAppraisalsToReview()
    {
        return $this->supervisedAppraisals()
            ->where('status', 'submitted')
            ->get();
    }

    public function hasOutstandingAppraisal(): bool
    {
        return $this->performanceAppraisals()
            ->whereIn('status', ['draft', 'submitted'])
            ->exists();
    }



    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('employee_code', 'like', "%{$search}%");
        });
    }




    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the current year's leave balances
     */
    public function currentLeaveBalances()
    {
        return $this->leaveBalances()
            ->where('year', now()->year)
            ->with('leaveType');
    }

    /**
     * Get the available leave balance for a specific leave type
     */
    public function getAvailableLeaveBalance($leaveTypeId)
    {
        return $this->leaveBalances()
            ->where([
                'leave_type_id' => $leaveTypeId,
                'year' => now()->year
            ])
            ->first();
    }

    /**
     * Check if employee has sufficient leave balance
     */
    public function hasLeaveBalance($leaveTypeId, $requestedDays): bool
    {
        $balance = $this->getAvailableLeaveBalance($leaveTypeId);
        if (!$balance) {
            return false;
        }
        return ($balance->entitled_days + $balance->carried_forward_days + $balance->additional_days
                - $balance->taken_days - $balance->pending_days) >= $requestedDays;
    }

    /**
     * Get pending leave requests
     */
    public function pendingLeaveRequests()
    {
        return $this->leaveRequests()
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Check if employee has overlapping leave requests
     */
    public function hasOverlappingLeaveRequests($startDate, $endDate, $excludeRequestId = null): bool
    {
        return $this->leaveRequests()
            ->where('status', '!=', 'rejected')
            ->where('status', '!=', 'cancelled')
            ->when($excludeRequestId, function ($query) use ($excludeRequestId) {
                return $query->where('id', '!=', $excludeRequestId);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
    }

    /**
     * Get all approved leave requests for a date range
     */
    public function getApprovedLeaveRequests($startDate, $endDate)
    {
        return $this->leaveRequests()
            ->where('status', 'approved')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate])
            ->get();
    }

    /**
     * Get leave requests that need department head approval
     */
    public function getLeaveRequestsNeedingApproval()
    {
        if (!$this->isDepartmentHead()) {
            return collect();
        }

        return LeaveRequest::whereHas('employee', function ($query) {
            $query->where('department_id', $this->department_id);
        })
            ->where('status', 'pending')
            ->where('employee_id', '!=', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get total leave days taken for a specific type in current year
     */
    public function getLeaveDaysTaken($leaveTypeId): int
    {
        $balance = $this->getAvailableLeaveBalance($leaveTypeId);
        return $balance ? $balance->taken_days : 0;
    }

    /**
     * Get total pending leave days for a specific type
     */
    public function getPendingLeaveDays($leaveTypeId): int
    {
        $balance = $this->getAvailableLeaveBalance($leaveTypeId);
        return $balance ? $balance->pending_days : 0;
    }

    // Keep your existing relationships and methods...

    // Update isDepartmentHead method to include additional checks

    public function scopeActiveEmployees($query)
    {
        return $query->where('employment_status', 'active')
            ->where('application_status', 'active');
    }

    // Add scope for department members
    public function scopeDepartmentMembers($query, $departmentId)
    {
        return $query->where('department_id', $departmentId)
            ->where('employment_status', 'active');
    }

    public function isDepartmentHead(): bool
    {
        if (!$this->user) {
            return false;
        }
        return $this->user->hasRole('department_head') &&
            $this->employment_status === 'active';
    }


// In Department.php - Add department head relationship
    public function departmentHead(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'department_head_id');
    }

    public function getLeaveBalance(int $leaveTypeId): float
    {
        return $this->leaveBalances()
            ->where('leave_type_id', $leaveTypeId)
            ->value('balance') ?? 0;
    }

    public function deductLeaveBalance(int $leaveTypeId, float $days): void
    {
        $balance = $this->leaveBalances()
            ->where('leave_type_id', $leaveTypeId)
            ->first();

        if ($balance) {
            $balance->update([
                'balance' => $balance->balance - $days,
                'used' => $balance->used + $days
            ]);
        }
    }






}
