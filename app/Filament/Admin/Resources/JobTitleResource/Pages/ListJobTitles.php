<?php

namespace App\Filament\Admin\Resources\JobTitleResource\Pages;

use App\Filament\Admin\Resources\JobTitleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobTitles extends ListRecords
{
    protected static string $resource = JobTitleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
