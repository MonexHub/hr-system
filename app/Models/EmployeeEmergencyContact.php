<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeEmergencyContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'relationship',
        'postal_address',
        'email',
        'workplace',
        'phone',
        'mobile',
        'county_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }
}
