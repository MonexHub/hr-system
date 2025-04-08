<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLoan extends Model
{
    protected $fillable = [
        'employee_id',
        'loan_type_id',
        'amount_requested',
        'amount_approved',
        'monthly_installment',
        'repayment_start_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount_requested' => 'decimal:2',
        'amount_approved' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'repayment_start_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function loanType()
    {
        return $this->belongsTo(LoanType::class);
    }
}
