<?php

namespace App\Filament\Employee\Resources\JobPostingResource\Pages;

use App\Filament\Employee\Resources\JobPostingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateJobPosting extends CreateRecord
{
    protected static string $resource = JobPostingResource::class;
}
