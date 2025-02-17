<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class JobTitle extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'department_id',
        'description',
        'net_salary_min',
        'net_salary_max',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'net_salary_min' => 'decimal:2',
        'net_salary_max' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the employees for this job title.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Scope a query to only include active job titles.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Get formatted net salary range.
     */
    public function getNetSalaryRangeAttribute(): string
    {
        if (!$this->net_salary_min && !$this->net_salary_max) {
            return 'Not specified';
        }

        if (!$this->net_salary_max) {
            return 'From TZS ' . number_format($this->net_salary_min, 2);
        }

        if (!$this->net_salary_min) {
            return 'Up to TZS ' . number_format($this->net_salary_max, 2);
        }

        return 'TZS ' . number_format($this->net_salary_min, 2) . ' - ' . number_format($this->net_salary_max, 2);
    }

    /**
     * Get the number of employees with this job title.
     */
    public function getEmployeeCountAttribute(): int
    {
        return $this->employees()->count();
    }

    /**
     * Get the validation rules that apply to the model.
     */
    public static function validationRules($id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:job_titles,name,' . $id],
            'description' => ['nullable', 'string'],
            'net_salary_min' => ['nullable', 'numeric', 'min:0'],
            'net_salary_max' => ['nullable', 'numeric', 'min:0', 'gte:net_salary_min'],
            'is_active' => ['boolean'],
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
