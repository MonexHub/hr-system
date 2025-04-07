<?php

namespace App\Jobs;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class ProcessLeaveApproval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    protected $leaveRequestId;
    protected $approverId;
    protected $remarks;
    protected $approvalType;

    /**
     * Create a new job instance.
     *
     * @param int $leaveRequestId
     * @param int $approverId
     * @param string|null $remarks
     * @param string $approvalType
     * @return void
     */
    public function __construct(int $leaveRequestId, int $approverId, ?string $remarks, string $approvalType)
    {
        $this->leaveRequestId = $leaveRequestId;
        $this->approverId = $approverId;
        $this->remarks = $remarks;
        $this->approvalType = $approvalType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            $leaveRequest = LeaveRequest::findOrFail($this->leaveRequestId);

            // Temporarily disable event listeners to prevent automatic notifications
            $originalDispatcher = $leaveRequest->getEventDispatcher();
            $leaveRequest->unsetEventDispatcher();

            $approver = User::findOrFail($this->approverId);

            switch ($this->approvalType) {
                case 'department':
                    $this->processDepartmentApproval($leaveRequest, $approver);
                    $message = 'Leave request has been approved by Department Head.';
                    break;
                case 'hr':
                    $this->processHRApproval($leaveRequest, $approver);
                    $message = $leaveRequest->isEmployeeDepartmentHead()
                        ? 'Leave request approved by HR and forwarded to CEO.'
                        : 'Leave request has been fully approved by HR.';
                    break;
                case 'ceo':
                    $this->processCEOApproval($leaveRequest, $approver);
                    $message = 'Leave request has been fully approved by CEO.';
                    break;
                case 'reject':
                    $this->processRejection($leaveRequest, $approver);
                    $message = 'Leave request has been rejected.';
                    break;
                case 'cancel':
                    $this->processCancellation($leaveRequest, $approver);
                    $message = 'Leave request has been cancelled.';
                    break;
                default:
                    throw new \Exception('Invalid approval type');
            }

            // Restore the event dispatcher after saving
            $leaveRequest->setEventDispatcher($originalDispatcher);

            DB::commit();

            // Send a success notification through Filament
            Notification::make()
                ->title('Success')
                ->body($message)
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process leave approval', [
                'leave_request_id' => $this->leaveRequestId,
                'approval_type' => $this->approvalType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Send an error notification through Filament
            Notification::make()
                ->title('Error')
                ->body('Failed to process approval. ' . $e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    /**
     * Process department head approval
     */
    private function processDepartmentApproval(LeaveRequest $leaveRequest, User $approver)
    {
        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            throw new \Exception('Leave request is not in pending status');
        }

        $leaveRequest->department_approved_by = $approver->id;
        $leaveRequest->department_approved_at = now();
        $leaveRequest->department_remarks = $this->remarks;
        $leaveRequest->status = LeaveRequest::STATUS_DEPARTMENT_APPROVED;
        $leaveRequest->save();

        // Manually send notification - we're bypassing the model's event
        $leaveRequest->notifyDepartmentApproval();
    }

    /**
     * Process HR manager approval
     */
    private function processHRApproval(LeaveRequest $leaveRequest, User $approver)
    {
        if (!in_array($leaveRequest->status, [LeaveRequest::STATUS_DEPARTMENT_APPROVED, LeaveRequest::STATUS_PENDING])) {
            throw new \Exception('Invalid request status for HR approval');
        }

        $leaveRequest->hr_approved_by = $approver->id;
        $leaveRequest->hr_approved_at = now();
        $leaveRequest->hr_remarks = $this->remarks;

        // Different flow for HODs vs regular employees
        if ($leaveRequest->isEmployeeDepartmentHead()) {
            $leaveRequest->status = LeaveRequest::STATUS_HR_APPROVED; // Move to CEO approval
            $leaveRequest->save();

            // Manually send notification - we're bypassing the model's event
            $leaveRequest->notifyHRApproval();
        } else {
            // For regular employees, this is final approval
            $leaveRequest->status = LeaveRequest::STATUS_APPROVED;
            $leaveRequest->save();

            // Update leave balance
            $leaveRequest->updateLeaveBalance();

            // Manually send notification - we're bypassing the model's event
            $leaveRequest->notifyFinalApproval();
        }
    }

    /**
     * Process CEO approval
     */
    private function processCEOApproval(LeaveRequest $leaveRequest, User $approver)
    {
        if ($leaveRequest->status !== LeaveRequest::STATUS_HR_APPROVED) {
            throw new \Exception('HR approval required first');
        }

        if (!$leaveRequest->isEmployeeDepartmentHead()) {
            throw new \Exception('CEO approval is only required for Department Heads');
        }

        $leaveRequest->ceo_approved_by = $approver->id;
        $leaveRequest->ceo_approved_at = now();
        $leaveRequest->ceo_remarks = $this->remarks;
        $leaveRequest->status = LeaveRequest::STATUS_APPROVED;
        $leaveRequest->save();

        // Update leave balance
        $leaveRequest->updateLeaveBalance();

        // Manually send notification - we're bypassing the model's event
        $leaveRequest->notifyFinalApproval();
    }

    /**
     * Process rejection
     */
    private function processRejection(LeaveRequest $leaveRequest, User $approver)
    {
        $leaveRequest->status = LeaveRequest::STATUS_REJECTED;
        $leaveRequest->rejection_reason = $this->remarks;
        $leaveRequest->save();

        // Release pending days from leave balance
        $leaveRequest->releaseLeaveBalance();

        // Manually send notification - we're bypassing the model's event
        if (method_exists($leaveRequest, 'notifyRejection')) {
            $leaveRequest->notifyRejection();
        }
    }

    /**
     * Process cancellation
     */
    private function processCancellation(LeaveRequest $leaveRequest, User $approver)
    {
        if (!in_array($leaveRequest->status, [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_DEPARTMENT_APPROVED])) {
            throw new \Exception('Leave request cannot be cancelled in current status');
        }

        $leaveRequest->status = LeaveRequest::STATUS_CANCELLED;
        $leaveRequest->cancellation_reason = $this->remarks;
        $leaveRequest->cancelled_by = $approver->id;
        $leaveRequest->cancelled_at = now();
        $leaveRequest->save();

        // Release pending days from leave balance
        $leaveRequest->releaseLeaveBalance();

        // Manually send notification - we're bypassing the model's event
        if (method_exists($leaveRequest, 'notifyCancellation')) {
            $leaveRequest->notifyCancellation();
        }
    }
}
