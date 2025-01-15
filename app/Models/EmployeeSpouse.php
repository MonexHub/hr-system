<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeSpouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'address',
        'po_box',
        'district_town_city',
        'county_id',
        'email',
        'phone',
        'mobile',
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
