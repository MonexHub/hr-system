<?php

namespace App\Filament\Admin\Resources\JobPostingResource\Pages;

use App\Filament\Admin\Resources\JobPostingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateJobPosting extends CreateRecord
{
    protected static string $resource = JobPostingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

}
