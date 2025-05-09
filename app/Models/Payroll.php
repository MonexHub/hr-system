<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'period',
        'gross_salary',
        'total_deductions',
        'total_benefits',
        'net_salary',
        'net_pay',
        'status',
    ];

    protected $casts = [
        'period' => 'date',
        'gross_salary' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_benefits' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollBenefits(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        // Use a custom query to fetch benefits for this payroll's employee
        return $this->hasOneThrough(
            EmployeeBenefit::class,
            Employee::class,
            'id', // Foreign key on Employee
            'employee_id', // Foreign key on EmployeeBenefit
            'employee_id', // Foreign key on Payroll
            'id' // Local key on Employee
        );
    }

    public function payrollDeductions(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        // Use a custom query to fetch deductions for this payroll's employee
        return $this->hasOneThrough(
            EmployeeDeduction::class,
            Employee::class,
            'id', // Foreign key on Employee
            'employee_id', // Foreign key on EmployeeDeduction
            'employee_id', // Foreign key on Payroll
            'id' // Local key on Employee
        );
    }

    // For backward compatibility, create these aliases
    public function benefits()
    {
        return $this->payrollBenefits();
    }

    public function deductions()
    {
        return $this->payrollDeductions();
    }

    public function financial()
    {
        return $this->hasOneThrough(
            EmployeeFinancial::class,
            Employee::class,
            'id', // Foreign key on EmployeeFinancial
            'id', // Foreign key on Payroll (employee_id)
            'employee_id', // Local key on Payroll
            'id' // Local key on Employee
        );
    }

    public function paymentLog()
    {
        return $this->hasOne(PayrollPaymentLog::class);
    }
}
