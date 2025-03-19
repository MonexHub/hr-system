<?php

namespace App\Events;

use App\Models\LeaveRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaveApprovalProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $leaveRequest;
    public $approvalType;
    public $success;
    public $message;
    public $userId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(LeaveRequest $leaveRequest, string $approvalType, bool $success, string $message, int $userId)
    {
        $this->leaveRequest = $leaveRequest;
        $this->approvalType = $approvalType;
        $this->success = $success;
        $this->message = $message;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }
}
