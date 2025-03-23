<?php

namespace App\Filament\Admin\Resources\NotificationPreferenceResource\Pages;

use App\Filament\Admin\Resources\NotificationPreferenceResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateNotificationPreference extends CreateRecord
{
    protected static string $resource = NotificationPreferenceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove the employees array from the data
        $employeeIds = $data['employees'];
        unset($data['employees']);

        // Store employee IDs for later use
        $this->employeeIds = $employeeIds;

        // Return data for the first employee
        $data['employee_id'] = $employeeIds[0];
        return $data;
    }

    protected function afterCreate(): void
    {
        // Create preferences for remaining employees
        foreach (array_slice($this->employeeIds, 1) as $employeeId) {
            $data = $this->form->getState();
            unset($data['employees']); // Remove the employees array

            $this->record::create([
                ...$data,
                'employee_id' => $employeeId,
            ]);
        }

        // Show success notification
        Notification::make()
            ->title('Notification preferences created')
            ->body('Notification preferences have been created for ' . count($this->employeeIds) . ' employees.')
            ->success()
            ->send();
    }
}
