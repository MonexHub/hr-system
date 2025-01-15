<?php

namespace App\Filament\Employee\Resources\JobPostingResource\Pages;

use App\Filament\Employee\Resources\JobPostingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobPostings extends ListRecords
{
    protected static string $resource = JobPostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
