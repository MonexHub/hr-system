<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Benefit extends Model
{
    protected $fillable = [
        'payroll_id',
        'benefit_id',
        'name',
        'type',
        'value',
        'amount',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function benefit()
    {
        return $this->belongsTo(Benefit::class);
    }
}
