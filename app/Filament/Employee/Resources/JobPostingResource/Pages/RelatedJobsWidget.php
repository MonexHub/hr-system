<?php

namespace App\Filament\Employee\Resources\JobPostingResource\Widgets;

use App\Models\JobPosting;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class RelatedJobsWidget extends Widget
{
    protected static string $view = 'filament.employee.resources.job-posting.widgets.related-jobs';

    public $record;

    public function getSimilarJobs(): \Illuminate\Database\Eloquent\Collection
    {
        return JobPosting::query()
            ->where('id', '!=', $this->record->id)
            ->where('status', 'published')
            ->where(function (Builder $query) {
                $query->where('department_id', $this->record->department_id)
                    ->orWhere('employment_type', $this->record->employment_type)
                    ->orWhere(function ($q) {
                        foreach ($this->record->skills_required as $skill) {
                            $q->orWhereJsonContains('skills_required', $skill);
                        }
                    });
            })
            ->limit(3)
            ->get();
    }
}
