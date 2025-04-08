<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'minimum_salary_required',
        'max_amount_cap',
        'repayment_months',
        'description',
    ];

    protected $casts = [
        'minimum_salary_required' => 'decimal:2',
        'max_amount_cap' => 'decimal:2',
    ];

    public function employeeLoans()
    {
        return $this->hasMany(EmployeeLoan::class);
    }
}
