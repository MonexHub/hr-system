<?php

namespace App\Models;

use App\Notifications\LeaveRequestNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_taken',
        'reason',
        'status',
        'attachments',
        'approved_by',
        'manager_approved_by',
        'rejection_reason',
        'approved_at',
        'manager_approved_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'attachments' => 'array',
    ];

    // Boot method for notifications
    protected static function booted()
    {
        static::created(function ($leaveRequest) {
            // Notify line manager
            $lineManager = $leaveRequest->employee->reportingTo?->user;
            if ($lineManager) {
                $lineManager->notify(new LeaveRequestNotification($leaveRequest, 'new_request'));
            }
        });

        static::updated(function ($leaveRequest) {
            // Manager approval notification
            if ($leaveRequest->isDirty('manager_approved_at') && $leaveRequest->manager_approved_at) {
                // Notify HR team
                User::role('hr')->get()->each(function ($hrUser) use ($leaveRequest) {
                    $hrUser->notify(new LeaveRequestNotification($leaveRequest, 'manager_approval'));
                });
            }

            // Final status update notification
            if ($leaveRequest->isDirty('approved_at') || $leaveRequest->isDirty('status')) {
                $leaveRequest->employee->user->notify(
                    new LeaveRequestNotification($leaveRequest, 'status_update')
                );

                // If rejected, also notify the line manager
                if ($leaveRequest->status === 'rejected' && $leaveRequest->employee->reportingTo?->user) {
                    $leaveRequest->employee->reportingTo->user->notify(
                        new LeaveRequestNotification($leaveRequest, 'request_rejected')
                    );
                }
            }
        });
    }

    // Status Management Methods
    public function approveByManager($managerId): void
    {
        $this->update([
            'manager_approved_by' => $managerId,
            'manager_approved_at' => now(),
            'status' => 'pending_hr'
        ]);
    }

    public function approveByHR($hrId): void
    {
        // First validate leave balance
        if (!$this->validateLeaveBalance()) {
            throw new \Exception('Insufficient leave balance');
        }

        // Update leave balance
        $leaveBalance = LeaveBalance::where('employee_id', $this->employee_id)
            ->where('leave_type_id', $this->leave_type_id)
            ->where('year', $this->start_date->year)
            ->first();

        if ($leaveBalance) {
            $leaveBalance->update([
                'days_taken' => $leaveBalance->days_taken + $this->days_taken,
                'days_remaining' => $leaveBalance->days_remaining - $this->days_taken
            ]);
        }

        $this->update([
            'approved_by' => $hrId,
            'approved_at' => now(),
            'status' => 'approved'
        ]);
    }

    public function reject($userId, $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $userId,
            'approved_at' => now()
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    // Helper Methods
    public function calculateDays(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function isAwaitingManagerApproval(): bool
    {
        return $this->status === 'pending' && !$this->manager_approved_at;
    }

    public function isAwaitingHRApproval(): bool
    {
        return $this->status === 'pending_hr' && $this->manager_approved_at && !$this->approved_at;
    }

    public function canBeApprovedBy(User $user): bool
    {
        if ($user->hasRole('hr') && $this->status === 'pending_hr') {
            return true;
        }

        if ($this->status === 'pending' &&
            $this->employee->reporting_to === $user->employee?->id) {
            return true;
        }

        return false;
    }

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function managerApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePendingHR($query)
    {
        return $query->where('status', 'pending_hr');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'pending_hr']);
    }


    public function validateLeaveBalance(): bool
    {
        // Check if employee has sufficient leave balance
        $leaveBalance = LeaveBalance::where('employee_id', $this->employee_id)
            ->where('leave_type_id', $this->leave_type_id)
            ->where('year', $this->start_date->year)
            ->first();

        if (!$leaveBalance || $this->days_taken > $leaveBalance->days_remaining) {
            return false;
        }

        return true;
    }
}
