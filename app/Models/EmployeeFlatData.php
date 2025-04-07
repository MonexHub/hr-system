<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeFlatData extends Model
{
    use SoftDeletes;

    protected $table = 'employee_flat_data';

    protected $fillable = [
        // Department fields
        'department_name',
        'department_code',
        'department_description',
        'department_parent_code',
        'department_manager_code',
        'department_organization_unit_code',
        'department_is_active',
        'department_phone',
        'department_email',
        'department_location',
        'department_annual_budget',
        'department_current_headcount',
        'department_max_headcount',

        // Job Title fields
        'job_title_name',
        'job_title_description',
        'job_title_net_salary_min',
        'job_title_net_salary_max',
        'job_title_is_active',

        // Employee fields
        'user_code',
        'employee_code',
        'application_status',
        'unit_code',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'birthdate',
        'marital_status',
        'profile_photo',
        'phone_number',
        'permanent_address',
        'city',
        'state',
        'postal_code',
        'job_title',
        'branch',
        'employment_status',
        'appointment_date',
        'contract_type',
        'terms_of_employment',
        'contract_end_date',
        'salary',
        'reporting_to_code',

    ];

    protected $casts = [
        // Boolean fields
        'department_is_active' => 'boolean',
        'job_title_is_active' => 'boolean',
        'certification_has_expiry' => 'boolean',
        'experience_is_current' => 'boolean',

        // Date fields
        'birthdate' => 'date',
        'appointment_date' => 'date',
        'contract_end_date' => 'date',
        'education_start_date' => 'date',
        'education_end_date' => 'date',
        'certification_issue_date' => 'date',
        'certification_expiry_date' => 'date',
        'experience_start_date' => 'date',
        'experience_end_date' => 'date',
        'training_start_date' => 'date',
        'training_end_date' => 'date',

        // Decimal fields
        'department_annual_budget' => 'decimal:2',
        'job_title_net_salary_min' => 'decimal:2',
        'job_title_net_salary_max' => 'decimal:2',
        'salary' => 'decimal:2',
        'education_grade' => 'decimal:2',

        // Integer fields
        'department_current_headcount' => 'integer',
        'department_max_headcount' => 'integer',
        'document_file_size' => 'integer',
        'skill_years_of_experience' => 'integer',
    ];

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('application_status', 'active');
    }

    public function scopeByDepartment($query, $departmentCode)
    {
        return $query->where('department_code', $departmentCode);
    }

    public function scopeByJobTitle($query, $jobTitle)
    {
        return $query->where('job_title_name', $jobTitle);
    }

    // Accessor for full name
    public function getFullNameAttribute()
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ])));
    }

    // Accessor for age
    public function getAgeAttribute()
    {
        return $this->birthdate ? $this->birthdate->age : null;
    }

    // Accessor for service duration
    public function getServiceDurationAttribute()
    {
        return $this->appointment_date ? $this->appointment_date->diffInYears(now()) : 0;
    }
}
