<?php

namespace App\Models;

use App\Notifications\LeaveRequestNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification as FilamentNotification;

class LeaveRequest extends Model
{
    use SoftDeletes, Notifiable;

    protected $fillable = [
        'request_number',
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'attachment_path',
        'status',
        'department_approved_by',
        'department_approved_at',
        'department_remarks',
        'hr_approved_by',
        'hr_approved_at',
        'hr_remarks',
        'ceo_approved_by',
        'ceo_approved_at',
        'ceo_remarks',
        'rejection_reason',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'department_approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
        'ceo_approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'total_days' => 'float'
    ];

    protected $with = ['employee', 'employee.department', 'leaveType'];

    const STATUS_PENDING = 'pending';
    const STATUS_DEPARTMENT_APPROVED = 'department_approved';
    const STATUS_HR_APPROVED = 'hr_approved';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($leaveRequest) {
            try {
                DB::beginTransaction();

                if (!$leaveRequest->validateLeaveBalance()) {
                    DB::rollBack();
                    return false;
                }

                if (!$leaveRequest->validateOverlappingLeaves()) {
                    DB::rollBack();
                    return false;
                }

                $leaveRequest->request_number = static::generateRequestNumber();
                $leaveRequest->created_by = auth()->id();
                $leaveRequest->status = self::STATUS_PENDING;

                if (!$leaveRequest->relationLoaded('employee')) {
                    $leaveRequest->load('employee');
                }

                if ($leaveRequest->isEmployeeDepartmentHead()) {
                    $leaveRequest->status = self::STATUS_DEPARTMENT_APPROVED;
                    $leaveRequest->department_approved_by = auth()->id();
                    $leaveRequest->department_approved_at = now();
                }

                DB::commit();
                return true;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error creating leave request', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                FilamentNotification::make()
                    ->title('Error Creating Request')
                    ->body('An error occurred while creating the leave request. Please try again.')
                    ->danger()
                    ->send();

                return false;
            }
        });

        static::created(function ($leaveRequest) {
            try {
                if ($leaveRequest->isEmployeeDepartmentHead()) {
                    static::notifyHRManagers($leaveRequest);
                } else {
                    static::notifyDepartmentHead($leaveRequest);
                }
            } catch (\Exception $e) {
                Log::error('Error sending notifications', [
                    'leave_request_id' => $leaveRequest->id,
                    'error' => $e->getMessage()
                ]);
            }
        });

        static::updating(function ($leaveRequest) {
            if ($leaveRequest->isDirty(['start_date', 'end_date']) && !$leaveRequest->validateOverlappingLeaves()) {
                return false;
            }
        });

        static::updated(function ($leaveRequest) {
            // Only process notifications if skipNotifications flag is false
            if ($leaveRequest->isDirty('status') && !$leaveRequest->skipNotifications) {
                $leaveRequest->handleStatusChangeNotifications();
            }
        });
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

    public function departmentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'department_approved_by');
    }

    public function hrApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }

    public function ceoApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ceo_approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Validation Methods
    protected function validateLeaveBalance(): bool
    {
        $balance = $this->employee->leaveBalances()
            ->where('leave_type_id', $this->leave_type_id)
            ->where('year', Carbon::parse($this->start_date)->year)
            ->first();

        if (!$balance) {
            FilamentNotification::make()
                ->title('Leave Balance Not Found')
                ->body('No leave balance found for this leave type in the selected year.')
                ->danger()
                ->send();
            return false;
        }

        $availableBalance = $balance->entitled_days +
            $balance->carried_forward_days +
            $balance->additional_days -
            $balance->taken_days -
            $balance->pending_days;

        if ($this->total_days > $availableBalance) {
            FilamentNotification::make()
                ->title('Insufficient Leave Balance')
                ->body("You have requested {$this->total_days} days, but only have {$availableBalance} days available.")
                ->danger()
                ->send();
            return false;
        }

        // Reserve the days in pending balance
        $balance->pending_days += $this->total_days;
        $balance->save();

        return true;
    }

    protected function validateOverlappingLeaves(): bool
    {
        $existingLeave = self::where('employee_id', $this->employee_id)
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                    ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                    ->orWhere(function ($query) {
                        $query->where('start_date', '<=', $this->start_date)
                            ->where('end_date', '>=', $this->end_date);
                    });
            })
            ->whereIn('status', [
                self::STATUS_PENDING,
                self::STATUS_DEPARTMENT_APPROVED,
                self::STATUS_HR_APPROVED,
                self::STATUS_APPROVED
            ])
            ->first();

        if ($existingLeave) {
            $startDate = $existingLeave->start_date->format('M d, Y');
            $endDate = $existingLeave->end_date->format('M d, Y');

            FilamentNotification::make()
                ->title('Overlapping Leave Request')
                ->body("You already have a {$existingLeave->status} leave request from {$startDate} to {$endDate}.")
                ->warning()
                ->send();

            return false;
        }

        return true;
    }

    // Approval Methods
    public function approveDepartment(User $approver, ?string $remarks = null): void
    {
        try {
            DB::beginTransaction();

            if ($this->status !== self::STATUS_PENDING) {
                throw new \Exception('Leave request is not in pending status');
            }

            $this->department_approved_by = $approver->id;
            $this->department_approved_at = now();
            $this->department_remarks = $remarks;
            $this->status = self::STATUS_DEPARTMENT_APPROVED;
            $this->save();

            // Notify HR managers
            static::notifyHRManagers($this);

            FilamentNotification::make()
                ->title('Leave Request Approved')
                ->body('Request has been approved by Department Head and forwarded to HR.')
                ->success()
                ->send();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function approveHR(User $approver, ?string $remarks = null): void
    {
        try {
            DB::beginTransaction();

            if (!in_array($this->status, [self::STATUS_DEPARTMENT_APPROVED, self::STATUS_PENDING])) {
                throw new \Exception('Invalid request status for HR approval');
            }

            $this->hr_approved_by = $approver->id;
            $this->hr_approved_at = now();
            $this->hr_remarks = $remarks;

            // Different flow for HODs vs regular employees
            if ($this->isEmployeeDepartmentHead()) {
                $this->status = self::STATUS_HR_APPROVED; // Move to CEO approval
                $this->save();

                // Notify CEO for approval
                static::notifyCEO($this);
            } else {
                // For regular employees, this is final approval
                $this->status = self::STATUS_APPROVED;
                $this->save();

                // Update leave balance
                $this->updateLeaveBalance();

                // Send final approval notification
                $this->notifyFinalApproval();
            }

            FilamentNotification::make()
                ->title('Leave Request Processed')
                ->body($this->isEmployeeDepartmentHead()
                    ? 'Request has been approved by HR and forwarded to CEO for final approval.'
                    : 'Request has been fully approved.')
                ->success()
                ->send();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function approveCEO(User $approver, ?string $remarks = null): void
    {
        try {
            DB::beginTransaction();

            if ($this->status !== self::STATUS_HR_APPROVED) {
                throw new \Exception('HR approval required first');
            }

            if (!$this->isEmployeeDepartmentHead()) {
                throw new \Exception('CEO approval is only required for Department Heads');
            }

            $this->ceo_approved_by = $approver->id;
            $this->ceo_approved_at = now();
            $this->ceo_remarks = $remarks;
            $this->status = self::STATUS_APPROVED;
            $this->save();

            // Update leave balance
            $this->updateLeaveBalance();

            // Send final approval notification
            $this->notifyFinalApproval();

            FilamentNotification::make()
                ->title('Leave Request Approved')
                ->body('Request has been fully approved by CEO.')
                ->success()
                ->send();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reject(User $rejector, string $reason): void
    {
        try {
            DB::beginTransaction();

            $this->status = self::STATUS_REJECTED;
            $this->rejection_reason = $reason;
            $this->save();

            // Release pending days from leave balance
            $this->releaseLeaveBalance();

            FilamentNotification::make()
                ->title('Leave Request Rejected')
                ->body('The leave request has been rejected.')
                ->warning()
                ->send();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rejection failed', [
                'leave_request_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            FilamentNotification::make()
                ->title('Rejection Failed')
                ->body('There was an error processing the rejection. Please try again.')
                ->danger()
                ->send();

            throw $e;
        }
    }

    public function cancel(User $canceller, string $reason): void
    {
        try {
            DB::beginTransaction();

            if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_DEPARTMENT_APPROVED])) {
                throw new \Exception('Leave request cannot be cancelled in current status');
            }

            $this->status = self::STATUS_CANCELLED;
            $this->cancellation_reason = $reason;
            $this->cancelled_by = $canceller->id;
            $this->cancelled_at = now();
            $this->save();

            // Release pending days from leave balance
            $this->releaseLeaveBalance();

            FilamentNotification::make()
                ->title('Leave Request Cancelled')
                ->body('The leave request has been successfully cancelled.')
                ->success()
                ->send();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cancellation failed', [
                'leave_request_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            FilamentNotification::make()
                ->title('Cancellation Failed')
                ->body('There was an error cancelling the request. Please try again.')
                ->danger()
                ->send();

            throw $e;
        }
    }

    // Balance Management Methods
    public function updateLeaveBalance(): void
    {
        try {
            $balance = $this->employee->leaveBalances()
                ->where('leave_type_id', $this->leave_type_id)
                ->where('year', $this->start_date->year)
                ->first();


            if ($balance) {
                $balance->taken_days += $this->total_days;
                $balance->pending_days -= $this->total_days;
                $balance->save();

                FilamentNotification::make()
                    ->title('Leave Balance Updated')
                    ->body("Leave balance has been updated. Remaining balance: " .
                        ($balance->entitled_days + $balance->carried_forward_days +
                            $balance->additional_days - $balance->taken_days - $balance->pending_days) .
                        " days")
                    ->info()
                    ->send();
            }
        } catch (\Exception $e) {
            Log::error('Failed to update leave balance', [
                'leave_request_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            FilamentNotification::make()
                ->title('Balance Update Failed')
                ->body('There was an error updating the leave balance. Please contact HR.')
                ->danger()
                ->send();

            throw $e;
        }
    }

    public function releaseLeaveBalance(): void
    {
        try {
            $balance = $this->employee->leaveBalances()
                ->where('leave_type_id', $this->leave_type_id)
                ->where('year', $this->start_date->year)
                ->first();

            if ($balance) {
                $balance->pending_days -= $this->total_days;
                $balance->save();
            }
        } catch (\Exception $e) {
            Log::error('Failed to release leave balance', [
                'leave_request_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // Status Check Methods
    public function isEmployeeDepartmentHead(): bool
    {
        if (!$this->relationLoaded('employee')) {
            $this->load('employee');
        }
        if ($this->employee && !$this->employee->relationLoaded('user')) {
            $this->employee->load('user');
        }

        return $this->employee &&
            $this->employee->user &&
            $this->employee->user->hasRole('department_head');
    }

    public function requiresCEOApproval(): bool
    {
        return $this->isEmployeeDepartmentHead() ||
            ($this->leaveType && $this->leaveType->requires_ceo_approval);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isDepartmentApproved(): bool
    {
        return $this->status === self::STATUS_DEPARTMENT_APPROVED;
    }

    public function isHRApproved(): bool
    {
        return $this->status === self::STATUS_HR_APPROVED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_DEPARTMENT_APPROVED]) &&
            $this->employee_id === auth()->user()->employee->id;
    }

    // Query Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_REJECTED, self::STATUS_CANCELLED]);
    }

    // Notification Methods
    protected function handleStatusChangeNotifications(): void
    {
        try {
            switch ($this->status) {
                case self::STATUS_DEPARTMENT_APPROVED:
                    $this->notifyDepartmentApproval();
                    break;
                case self::STATUS_HR_APPROVED:
                    $this->notifyHRApproval();
                    break;
                case self::STATUS_APPROVED:
                    $this->notifyFinalApproval();
                    break;
                case self::STATUS_REJECTED:
                    $this->notifyRejection();
                    break;
                case self::STATUS_CANCELLED:
                    $this->notifyCancellation();
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Failed to process status change notifications', [
                'leave_request_id' => $this->id,
                'status' => $this->status,
                'error' => $e->getMessage()
            ]);
        }
    }
    public function notifyRejection(): void
    {
        try {
            // Notify the employee
            $this->employee->user->notify(new \App\Notifications\LeaveRequestNotification(
                $this,
                \App\Notifications\LeaveRequestNotification::TYPE_REJECTED,
                'Your leave request has been rejected.'
            ));

            // If rejected by department head
            if ($this->getOriginal('status') === self::STATUS_PENDING) {
                // Notify HR for reference
                $hrManagers = \App\Models\User::role('hr_manager')->get();
                \Illuminate\Support\Facades\Notification::send($hrManagers, new \App\Notifications\LeaveRequestNotification(
                    $this,
                    \App\Notifications\LeaveRequestNotification::TYPE_REJECTED,
                    "Leave request from {$this->employee->first_name} {$this->employee->last_name} has been rejected by department head."
                ));
            }
            // If rejected by HR
            elseif ($this->getOriginal('status') === self::STATUS_DEPARTMENT_APPROVED) {
                // Notify department head
                $departmentHead = \App\Models\User::role('department_head')
                    ->whereHas('employee', function ($query) {
                        $query->where('department_id', $this->employee->department_id);
                    })
                    ->first();

                if ($departmentHead) {
                    $departmentHead->notify(new \App\Notifications\LeaveRequestNotification(
                        $this,
                        \App\Notifications\LeaveRequestNotification::TYPE_REJECTED,
                        "Leave request from {$this->employee->first_name} {$this->employee->last_name} has been rejected by HR."
                    ));
                }
            }
            // If rejected by CEO
            elseif ($this->getOriginal('status') === self::STATUS_HR_APPROVED) {
                // Notify HR
                $hrManagers = \App\Models\User::role('hr_manager')->get();
                \Illuminate\Support\Facades\Notification::send($hrManagers, new \App\Notifications\LeaveRequestNotification(
                    $this,
                    \App\Notifications\LeaveRequestNotification::TYPE_REJECTED,
                    "Leave request from {$this->employee->first_name} {$this->employee->last_name} has been rejected by CEO."
                ));
            }

            \Filament\Notifications\Notification::make()
                ->title('Rejection Notification Sent')
                ->body('The employee has been notified of the rejection.')
                ->warning()
                ->send();

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send rejection notification', [
                'leave_request_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify about cancellation
     */
    public function notifyCancellation(): void
    {
        try {
            // Get the canceller name
            $canceller = \App\Models\User::find($this->cancelled_by);
            $cancellerName = $canceller ? $canceller->name : 'A user';

            // Determine if employee cancelled their own request
            $selfCancelled = $this->cancelled_by === $this->employee->user->id;

            // Message for notifications
            $message = $selfCancelled
                ? "Leave request has been cancelled by the employee."
                : "Leave request has been cancelled by {$cancellerName}.";

            // Employee notification (only if not self-cancelled)
            if (!$selfCancelled) {
                $this->employee->user->notify(new \App\Notifications\LeaveRequestNotification(
                    $this,
                    \App\Notifications\LeaveRequestNotification::TYPE_CANCELLED,
                    'Your leave request has been cancelled.'
                ));
            }

            // Notify relevant parties based on the previous status
            $previousStatus = $this->getOriginal('status');

            if ($previousStatus === self::STATUS_PENDING) {
                // Notify department head if not the canceller
                $departmentHead = \App\Models\User::role('department_head')
                    ->whereHas('employee', function ($query) {
                        $query->where('department_id', $this->employee->department_id);
                    })
                    ->first();

                if ($departmentHead && $departmentHead->id !== $this->cancelled_by) {
                    $departmentHead->notify(new \App\Notifications\LeaveRequestNotification(
                        $this,
                        \App\Notifications\LeaveRequestNotification::TYPE_CANCELLED,
                        $message
                    ));
                }
            }
            elseif ($previousStatus === self::STATUS_DEPARTMENT_APPROVED) {
                // Notify HR managers if not the canceller
                $hrManagers = \App\Models\User::role('hr_manager')
                    ->where('id', '!=', $this->cancelled_by)
                    ->get();

                \Illuminate\Support\Facades\Notification::send($hrManagers, new \App\Notifications\LeaveRequestNotification(
                    $this,
                    \App\Notifications\LeaveRequestNotification::TYPE_CANCELLED,
                    $message
                ));

                // Also notify department head if they approved it and aren't the canceller
                if ($this->department_approved_by && $this->department_approved_by !== $this->cancelled_by) {
                    $departmentApprover = \App\Models\User::find($this->department_approved_by);
                    if ($departmentApprover) {
                        $departmentApprover->notify(new \App\Notifications\LeaveRequestNotification(
                            $this,
                            \App\Notifications\LeaveRequestNotification::TYPE_CANCELLED,
                            $message
                        ));
                    }
                }
            }

            // Log the cancellation
            \Illuminate\Support\Facades\Log::info('Leave request cancelled', [
                'leave_request_id' => $this->id,
                'cancelled_by' => $this->cancelled_by,
                'previous_status' => $previousStatus,
                'cancelled_at' => $this->cancelled_at
            ]);

            // Display UI notification
            \Filament\Notifications\Notification::make()
                ->title('Cancellation Processed')
                ->body('The leave request has been cancelled and all relevant parties have been notified.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send cancellation notifications', [
                'leave_request_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            \Filament\Notifications\Notification::make()
                ->title('Notification Error')
                ->body('The cancellation was processed but there was an error sending some notifications.')
                ->warning()
                ->send();
        }
    }
    protected static function notifyDepartmentHead(LeaveRequest $leaveRequest): void
    {
        try {
            $departmentHead = User::role('department_head')
                ->whereHas('employee', function ($query) use ($leaveRequest) {
                    $query->where('department_id', $leaveRequest->employee->department_id);
                })
                ->first();

            if ($departmentHead) {
                $departmentHead->notify(new LeaveRequestNotification(
                    $leaveRequest,
                    LeaveRequestNotification::TYPE_PENDING_HOD
                ));
            } else {
                Log::warning('No department head found', [
                    'department_id' => $leaveRequest->employee->department_id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify department head', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public static function notifyHRManagers(LeaveRequest $leaveRequest): void
    {
        try {
            $hrManagers = User::role('hr_manager')->get();
            Notification::send($hrManagers, new LeaveRequestNotification(
                $leaveRequest,
                LeaveRequestNotification::TYPE_PENDING_HR
            ));
        } catch (\Exception $e) {
            Log::error('Failed to notify HR managers', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public static function notifyCEO(LeaveRequest $leaveRequest): void
    {
        try {
            $ceo = User::role('chief_executive_officer')->first();
            if ($ceo) {
                $ceo->notify(new LeaveRequestNotification(
                    $leaveRequest,
                    LeaveRequestNotification::TYPE_PENDING_CEO
                ));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify CEO', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Utility Methods
    public static function generateRequestNumber(): string
    {
        $prefix = 'LR';
        $year = date('Y');
        $lastRequest = self::whereYear('created_at', $year)->latest()->first();
        $sequence = $lastRequest ? intval(substr($lastRequest->request_number, -5)) + 1 : 1;
        return $prefix . $year . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function notifyDepartmentApproval(): void
    {
        try {
            $employeeName = "{$this->employee->first_name} {$this->employee->last_name}";

            // Notify employee
            $this->employee->user->notify(new LeaveRequestNotification(
                $this,
                LeaveRequestNotification::TYPE_PENDING_HR,
                'Your leave request has been approved by the Department Head and is now pending HR review.'
            ));

            // Notify HR managers
            $hrManagers = User::role('hr_manager')->get();
            Notification::send($hrManagers, new LeaveRequestNotification(
                $this,
                LeaveRequestNotification::TYPE_PENDING_HR,
                "A leave request from {$employeeName} requires your review."
            ));

        } catch (\Exception $e) {
            Log::error('Failed to send department approval notifications', [
                'leave_request_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function notifyHRApproval(): void
    {
        try {
            $employeeName = "{$this->employee->first_name} {$this->employee->last_name}";
            $needsCEOApproval = $this->requiresCEOApproval();

            $message = $needsCEOApproval
                ? 'Your leave request has been approved by HR and is now pending CEO approval.'
                : 'Your leave request has been approved by HR.';

            // Notify employee
            $this->employee->user->notify(new LeaveRequestNotification(
                $this,
                $needsCEOApproval ? LeaveRequestNotification::TYPE_PENDING_CEO : LeaveRequestNotification::TYPE_APPROVED,
                $message
            ));

            // If CEO approval is required, notify CEO
            if ($needsCEOApproval) {
                $ceo = User::role('chief_executive_officer')->first();
                if ($ceo) {
                    $ceo->notify(new LeaveRequestNotification(
                        $this,
                        LeaveRequestNotification::TYPE_PENDING_CEO,
                        "A leave request from {$employeeName} requires your approval."
                    ));
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send HR approval notifications', [
                'leave_request_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }


    protected function finalizeApproval(?string $remarks = null): void
    {
        try {
            DB::beginTransaction();
            // Update status and appropriate remarks column based on approval stage
            $updateData = ['status' => self::STATUS_APPROVED];

            if ($remarks) {
                if ($this->status === self::STATUS_HR_APPROVED) {
                    $updateData['ceo_remarks'] = $remarks;
                    $updateData['ceo_approved_by'] = auth()->id();
                    $updateData['ceo_approved_at'] = now();
                } elseif ($this->status === self::STATUS_DEPARTMENT_APPROVED) {
                    $updateData['hr_remarks'] = $remarks;
                    $updateData['hr_approved_by'] = auth()->id();
                    $updateData['hr_approved_at'] = now();
                }
            }

            $this->update($updateData);

            // Update leave balance
            $balance = $this->employee->leaveBalances()
                ->where('leave_type_id', $this->leave_type_id)
                ->where('year', $this->start_date->year)
                ->first();

            if ($balance) {
                $balance->convertPendingToTaken($this->total_days);
            }

            // Send final approval notification
            $this->employee->user->notify(new LeaveRequestNotification(
                $this,
                LeaveRequestNotification::TYPE_APPROVED,
                'Your leave request has been fully approved.'
            ));

            FilamentNotification::make()
                ->title('Leave Request Approved')
                ->body('The leave request has been approved and leave balance has been updated.')
                ->success()
                ->send();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to finalize approval', [
                'leave_request_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function isEmployeeHRManager(): bool
    {
        if (!$this->relationLoaded('employee')) {
            $this->load('employee');
        }
        if ($this->employee && !$this->employee->relationLoaded('user')) {
            $this->employee->load('user');
        }

        return $this->employee &&
            $this->employee->user &&
            $this->employee->user->hasRole('hr_manager');
    }

    public function notifyFinalApproval(): void
    {
        try {
            // Notify the employee
            $this->employee->user->notify(new LeaveRequestNotification(
                $this,
                LeaveRequestNotification::TYPE_APPROVED,
                'Your leave request has been fully approved.'
            ));

            // Notify the department head (if not the employee)
            if (!$this->isEmployeeDepartmentHead()) {
                $departmentHead = User::role('department_head')
                    ->whereHas('employee', function ($query) {
                        $query->where('department_id', $this->employee->department_id);
                    })
                    ->first();

                if ($departmentHead) {
                    $departmentHead->notify(new LeaveRequestNotification(
                        $this,
                        LeaveRequestNotification::TYPE_APPROVED,
                        "Leave request from {$this->employee->first_name} {$this->employee->last_name} has been approved."
                    ));
                }
            }

            // Notify HR managers of the final approval
            $hrManagers = User::role('hr_manager')->get();
            Notification::send($hrManagers, new LeaveRequestNotification(
                $this,
                LeaveRequestNotification::TYPE_APPROVED,
                "Leave request from {$this->employee->first_name} {$this->employee->last_name} has been fully approved."
            ));

            // Display success notification in the UI
            FilamentNotification::make()
                ->title('Final Approval Complete')
                ->body('The leave request has been fully approved and all relevant parties have been notified.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('Failed to send final approval notifications', [
                'leave_request_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            FilamentNotification::make()
                ->title('Notification Error')
                ->body('The approval was processed but there was an error sending some notifications.')
                ->warning()
                ->send();
        }

    }
    public function getApprovalTimeline(): array
    {
        return array_filter([
            'Submitted' => $this->created_at,
            'Department Approved' => $this->department_approved_at,
            'HR Approved' => $this->hr_approved_at,
            'CEO Approved' => $this->ceo_approved_at,
            'Rejected' => $this->isRejected() ? $this->updated_at : null,
            'Cancelled' => $this->cancelled_at,
        ]);
    }
}
