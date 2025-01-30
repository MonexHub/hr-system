<?php

namespace App\Filament\Admin\Resources\JobTitleResource\Pages;

use App\Filament\Admin\Resources\JobTitleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateJobTitle extends CreateRecord
{
    protected static string $resource = JobTitleResource::class;
}
