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

    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class);
    }

    public function companyExperiences(): HasMany
    {
        return $this->hasMany(CompanyExperience::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
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
        return "{$this->first_name} {$this->last_name}";
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

}
