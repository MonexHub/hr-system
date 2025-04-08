<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    protected $fillable = [
        'name',
        'type',
        'frequency',
        'execution_date',
        'active',
    ];

    protected $casts = [
        'execution_date' => 'date',
        'active' => 'boolean',
    ];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
