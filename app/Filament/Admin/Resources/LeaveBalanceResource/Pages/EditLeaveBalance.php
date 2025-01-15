<?php

namespace App\Filament\Admin\Resources\LeaveBalanceResource\Pages;

use App\Filament\Admin\Resources\LeaveBalanceResource;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;

class EditLeaveBalance extends EditRecord
{
    protected static string $resource = LeaveBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),

            Actions\Action::make('approve')
                ->action(function () {
                    $this->record->approve(auth()->id());
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->requiresConfirmation()
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending'),

            Actions\Action::make('reject')
                ->form([
                 Textarea::make('rejection_reason')
                        ->required()
                        ->label('Reason for Rejection'),
                ])
                ->action(function (array $data) {
                    $this->record->reject($data['rejection_reason']);
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->color('danger')
                ->visible(fn () => $this->record->status === 'pending'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update days_taken if dates changed
        if (isset($data['start_date']) && isset($data['end_date'])) {
            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $endDate = \Carbon\Carbon::parse($data['end_date']);
            $data['days_taken'] = $startDate->diffInDays($endDate) + 1;
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        // Prevent editing of approved/rejected requests
        if (in_array($this->record->status, ['approved', 'rejected'])) {
            $this->halt();
        }
    }
}
