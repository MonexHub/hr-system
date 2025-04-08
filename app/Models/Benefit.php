<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Benefit extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'applies_to_all',
        'type',
        'value',
    ];

    protected $casts = [
        'applies_to_all' => 'boolean',
        'value' => 'decimal:2',
    ];

    public function employeeBenefits()
    {
        return $this->hasMany(EmployeeBenefit::class);
    }
}
