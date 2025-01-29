<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeFinancial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'bank_name',
        'account_number',
        'branch_name',
        'insurance_provider',
        'insurance_number',
        'insurance_expiry_date',
        'nssf_number',
        'nssf_registration_date',
        'description'
    ];

    protected $casts = [
        'insurance_expiry_date' => 'date',
        'nssf_registration_date' => 'date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
