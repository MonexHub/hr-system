<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payee extends Model
{
    protected $fillable = [
        'min_amount',
        'max_amount',
        'rate',
        'fixed_amount',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
    ];

    /**
     * Determine tax for a given salary using this bracket.
     */
    public function calculateTaxFor(float $salary): float
    {
        if ($salary < $this->min_amount) {
            return 0;
        }

        if ($this->max_amount && $salary > $this->max_amount) {
            $taxable = $this->max_amount - $this->min_amount;
        } else {
            $taxable = $salary - $this->min_amount;
        }

        return ($taxable * ($this->rate / 100)) + $this->fixed_amount;
    }

    public function calculatePAYE($grossSalary)
		{
    return Payee::orderBy('min_amount')->get()
        ->reduce(function ($carry, $bracket) use ($grossSalary) {
            return $grossSalary >= $bracket->min_amount &&
                   (!$bracket->max_amount || $grossSalary <= $bracket->max_amount)
                ? $bracket->calculateTaxFor($grossSalary)
                : $carry;
        }, 0);
		}

}
