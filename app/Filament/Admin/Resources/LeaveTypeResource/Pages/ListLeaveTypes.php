<?php

namespace App\Filament\Admin\Resources\LeaveTypeResource\Pages;

use App\Filament\Admin\Resources\LeaveTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Notifications\Notification;

class ListLeaveTypes extends ListRecords
{
    protected static string $resource = LeaveTypeResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If this leave type has associated requests, prevent certain changes
        if ($this->record->leaveRequests()->exists()) {
            unset($data['max_days_per_year']);
            unset($data['is_paid']);
        }

        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Leave Type Updated')
            ->body('The leave type has been updated successfully.');
    }

    protected function beforeSave(): void
    {
        if ($this->record->leaveRequests()->exists() &&
            ($this->data['max_days_per_year'] !== $this->record->max_days_per_year ||
                $this->data['is_paid'] !== $this->record->is_paid)) {

            Notification::make()
                ->warning()
                ->title('Limited Updates')
                ->body('Some fields cannot be modified because this leave type has associated requests.')
                ->send();
        }
    }
}
