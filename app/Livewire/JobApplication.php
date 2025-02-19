<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\JobPosting;
use Livewire\Component;
use Livewire\WithPagination;

class JobApplication extends Component
{
    use WithPagination;

    public $search = '';
    public $department = '';
    public $type = '';
    public $departments;

    protected $queryString = [
        'search' => ['except' => ''],
        'department' => ['except' => ''],
        'type' => ['except' => '']
    ];

    public function mount()
    {
        // Load departments in mount
        $this->departments = Department::orderBy('name')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.job-listing', [
            'jobs' => JobPosting::published()
                ->with('department')
                ->when($this->search, function ($query) {
                    $query->where('title', 'like', '%' . $this->search . '%');
                })
                ->when($this->department, function ($query) {
                    $query->where('department_id', $this->department);
                })
                ->when($this->type, function ($query) {
                    $query->where('employment_type', $this->type);
                })
                ->latest()
                ->paginate(10),
            'departments' => Department::orderBy('name')->get()
        ]);
    }

}
