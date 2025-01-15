<?php

namespace App\Filament\Employee\Widgets;

use App\Models\JobPosting;
use Filament\Widgets\Widget;

class SimilarJobs extends Widget
{
    protected static string $view = 'filament.employee.widgets.similar-jobs';

    public function getViewData(): array
    {
        $currentJobPosting = request()->route('record');

        return [
            'similarJobs' => JobPosting::where('department_id', $currentJobPosting->department_id)
                ->where('id', '!=', $currentJobPosting->id)
                ->where('status', 'published')
                ->limit(3)
                ->get()
        ];
    }
}


