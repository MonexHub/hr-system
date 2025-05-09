<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payee extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_amount',
        'max_amount',
        'rate',
        'fixed_amount',
        'description',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
    ];

    /**
     * Calculate tax for a given salary using this tax bracket.
     *
     * @param float $salary The gross salary amount
     * @return float The calculated tax amount
     */
    public function calculateTaxFor(float $salary): float
    {
        // If salary is below this bracket's minimum, no tax applies from this bracket
        if ($salary < $this->min_amount) {
            return 0;
        }

        // Determine the taxable amount (portion of salary that falls within this bracket)
        if ($this->max_amount && $salary > $this->max_amount) {
            $taxable = $this->max_amount - $this->min_amount;
        } else {
            $taxable = $salary - $this->min_amount;
        }

        // Calculate tax: (taxable amount × rate) + fixed amount
        return ($taxable * ($this->rate / 100)) + $this->fixed_amount;
    }

    /**
     * Calculate PAYE tax for a given gross salary using the appropriate bracket.
     *
     * @param float $grossSalary The gross salary amount
     * @return float The calculated PAYE tax amount
     */
    public function calculatePAYE(float $grossSalary): float
    {
        return static::orderBy('min_amount')->get()
            ->reduce(function ($carry, $bracket) use ($grossSalary) {
                if ($grossSalary >= $bracket->min_amount &&
                    (!$bracket->max_amount || $grossSalary <= $bracket->max_amount)) {
                    return $bracket->calculateTaxFor($grossSalary);
                }
                return $carry;
            }, 0);
    }

    /**
     * Format the minimum amount with currency symbol.
     *
     * @return string
     */
    public function getFormattedMinAmountAttribute(): string
    {
        return 'TSh ' . number_format($this->min_amount, 0);
    }

    /**
     * Format the maximum amount with currency symbol or show 'unlimited' for null.
     *
     * @return string
     */
    public function getFormattedMaxAmountAttribute(): string
    {
        if ($this->max_amount === null) {
            return 'No upper limit';
        }

        return 'TSh ' . number_format($this->max_amount, 0);
    }

    /**
     * Format the tax rate with percentage symbol.
     *
     * @return string
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate, 2) . '%';
    }

    /**
     * Format the fixed amount with currency symbol.
     *
     * @return string
     */
    public function getFormattedFixedAmountAttribute(): string
    {
        return 'TSh ' . number_format($this->fixed_amount, 0);
    }

    /**
     * Get the bracket range as a formatted string.
     *
     * @return string
     */
    public function getBracketRangeAttribute(): string
    {
        if ($this->max_amount === null) {
            return 'Over ' . $this->formatted_min_amount;
        }

        return $this->formatted_min_amount . ' - ' . $this->formatted_max_amount;
    }

    /**
     * Get the effective rate for this bracket.
     *
     * @return float
     */
    public function getEffectiveRateAttribute(): float
    {
        if ($this->min_amount <= 0) {
            return $this->rate;
        }

        $sampleSalary = $this->max_amount ?
            ($this->min_amount + $this->max_amount) / 2 :
            $this->min_amount * 1.5;

        $tax = $this->calculateTaxFor($sampleSalary);

        return ($tax / $sampleSalary) * 100;
    }

    /**
     * Scope a query to only include active brackets.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query;
    }

    /**
     * Scope a query to order brackets by minimum amount.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('min_amount', 'asc');
    }

    /**
     * Validate brackets to ensure there are no overlaps or gaps.
     *
     * @return array Issues found, empty if none
     */
    public static function validateBrackets(): array
    {
        $allBrackets = static::ordered()->get();
        $issues = [];
        $lastMax = 0;

        foreach ($allBrackets as $bracket) {
            // Check for gaps
            if ($bracket->min_amount > $lastMax && $lastMax > 0) {
                $issues[] = "Gap found between TSh " . number_format($lastMax, 0) .
                    " and TSh " . number_format($bracket->min_amount, 0);
            }

            // Check for overlaps
            foreach ($allBrackets as $otherBracket) {
                if ($bracket->id == $otherBracket->id) continue;

                // Skip if current bracket has no max and is the highest bracket
                if ($bracket->max_amount === null) continue;

                // Check for overlap
                if ($bracket->min_amount < $otherBracket->max_amount &&
                    $otherBracket->min_amount < $bracket->max_amount &&
                    $otherBracket->max_amount !== null) {
                    $issues[] = "Overlap between brackets starting at TSh " .
                        number_format($bracket->min_amount, 0) . " and TSh " .
                        number_format($otherBracket->min_amount, 0);
                }
            }

            // Update last max
            if ($bracket->max_amount !== null) {
                $lastMax = max($lastMax, $bracket->max_amount);
            } else {
                $lastMax = PHP_INT_MAX; // Consider it infinity
            }
        }

        return $issues;
    }
}
