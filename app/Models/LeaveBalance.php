<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class LeaveBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'entitled_days',
        'carried_forward_days',
        'additional_days',
        'taken_days',
        'pending_days',
        'year',
        'remarks',
        'created_by'
    ];

    protected $casts = [
        'entitled_days' => 'float',
        'carried_forward_days' => 'float',
        'additional_days' => 'float',
        'taken_days' => 'float',
        'pending_days' => 'float',
        'year' => 'integer'
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Balance Calculation Methods
    public function getAvailableBalanceAttribute(): float
    {
        return $this->entitled_days +
            $this->carried_forward_days +
            $this->additional_days -
            $this->taken_days -
            $this->pending_days;
    }

    public function getTotalEntitlementAttribute(): float
    {
        return $this->entitled_days +
            $this->carried_forward_days +
            $this->additional_days;
    }

    // Leave Management Methods
    public function addPendingDays(float $days): bool
    {
        if ($days > $this->available_balance) {
            return false;
        }

        $this->increment('pending_days', $days);
        return true;
    }

    public function removePendingDays(float $days): void
    {
        $this->decrement('pending_days', $days);
    }

    public function convertPendingToTaken(float $days): void
    {
        $this->decrement('pending_days', $days);
        $this->increment('taken_days', $days);
    }

    // Scopes
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('leaveType', function ($query) {
            $query->where('is_active', true);
        });
    }

    public function scopeWithAvailableBalance(Builder $query): Builder
    {
        return $query->whereRaw('
            (entitled_days + carried_forward_days + additional_days - taken_days - pending_days) > 0
        ');
    }

    // Validation Methods
    public function hasEnoughBalance(float $requestedDays): bool
    {
        return $this->available_balance >= $requestedDays;
    }

    public function validateAndReserveDays(float $requestedDays): bool
    {
        if (!$this->hasEnoughBalance($requestedDays)) {
            return false;
        }

        return $this->addPendingDays($requestedDays);
    }

    // Helper Methods
    public static function initializeForEmployee(
        Employee $employee,
        LeaveType $leaveType,
        int $year,
        float $entitledDays = 0,
        float $carriedForwardDays = 0
    ): self {
        return self::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'entitled_days' => $entitledDays,
            'carried_forward_days' => $carriedForwardDays,
            'additional_days' => 0,
            'taken_days' => 0,
            'pending_days' => 0,
            'year' => $year,
            'created_by' => auth()->id()
        ]);
    }
}
