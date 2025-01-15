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
        'category',
        'description',
        'competency_level',
        'experience_qualification',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
