<?php

namespace App\Filament\Admin\Resources\InterviewScheduleResource\Pages;

use App\Filament\Admin\Resources\InterviewScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInterviewSchedules extends ListRecords
{
    protected static string $resource = InterviewScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
