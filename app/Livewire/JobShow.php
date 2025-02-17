<?php

namespace App\Livewire;

use App\Models\JobPosting;
use Livewire\Component;

class JobShow extends Component
{
    public JobPosting $jobPosting;

    public function mount(JobPosting $jobPosting)
    {
        abort_if(!$jobPosting->isOpen(), 404);
        $this->jobPosting = $jobPosting;
    }

    public function render()
    {
        return view('livewire.job-show');
    }

    public function apply()
    {
        return redirect()->route('jobs.apply', $this->jobPosting);
    }
}
