<?php

namespace App\Listeners;

use App\Events\DepartmentHeadcountChanged;
use Illuminate\Support\Facades\Log;

class LogDepartmentHeadcountChange
{
    /**
     * Handle the event.
     */
    public function handle(DepartmentHeadcountChanged $event): void
    {
        Log::info('Department headcount changed', [
            'department' => $event->department->name,
            'old_headcount' => $event->oldHeadcount,
            'new_headcount' => $event->newHeadcount,
            'change' => $event->newHeadcount - $event->oldHeadcount
        ]);
    }
}
