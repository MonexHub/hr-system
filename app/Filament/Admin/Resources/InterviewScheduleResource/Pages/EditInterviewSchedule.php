<?php

namespace App\Filament\Admin\Resources\InterviewScheduleResource\Pages;

use App\Filament\Admin\Resources\InterviewScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInterviewSchedule extends EditRecord
{
    protected static string $resource = InterviewScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
