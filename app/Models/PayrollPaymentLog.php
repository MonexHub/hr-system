<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPaymentLog extends Model
{
    protected $fillable = [
        'payroll_id',
        'reference_number',
        'amount',
        'status',
        'response_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'response_data' => 'array',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    /**
     * Check if the payment was successful
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the payment failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
