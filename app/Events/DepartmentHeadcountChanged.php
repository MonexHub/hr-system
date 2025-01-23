<?php

namespace App\Events;

use App\Models\Department;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepartmentHeadcountChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $department;
    public $oldHeadcount;
    public $newHeadcount;

    /**
     * Create a new event instance.
     */
    public function __construct(Department $department)
    {
        $this->department = $department;
        $this->oldHeadcount = $department->getOriginal('current_headcount');
        $this->newHeadcount = $department->current_headcount;
    }
}
