<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeSkill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'skill_name',
        'proficiency_level',
        'category',
        'description',
        'years_of_experience',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'proficiency_level' => 'string',
        'category' => 'string',
        'years_of_experience' => 'integer',
    ];

    /**
     * Get the valid proficiency levels.
     *
     * @return array
     */
    public static function getProficiencyLevels(): array
    {
        return ['beginner', 'intermediate', 'advanced', 'expert'];
    }

    /**
     * Get the valid categories.
     *
     * @return array
     */
    public static function getCategories(): array
    {
        return ['technical', 'soft_skills', 'languages', 'other'];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
