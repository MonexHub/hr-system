<?php

namespace App\Filament\Admin\Resources\LeaveRequestResource\Pages;

use App\Filament\Admin\Resources\LeaveRequestResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLeaveRequest extends EditRecord
{
    protected static string $resource = LeaveRequestResource::class;


    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Prevent editing if request is not in editable state
        if (!in_array($this->record->status, ['pending'])) {
            Notification::make()
                ->title('Not Editable')
                ->body('This leave request cannot be edited in its current status.')
                ->warning()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getBreadcrumb(): string
    {
        return 'Edit Leave Request';
    }
}
