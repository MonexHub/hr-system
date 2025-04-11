<?php

namespace App\Filament\Admin\Resources\PayeeResource\Pages;

use App\Filament\Admin\Resources\PayeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayee extends EditRecord
{
    protected static string $resource = PayeeResource::class;
    protected function getHeaderActions(): array
    {
        // Create a custom delete action as a safer alternative
        return [
            Actions\Action::make('delete')
                ->label('Delete')
                ->color('danger')
                ->icon('heroicon-m-trash')
                ->requiresConfirmation()
                ->modalHeading('Delete Tax Bracket')
                ->modalDescription('Are you sure you want to delete this tax bracket? This action cannot be undone.')
                ->modalSubmitActionLabel('Delete')
                ->action(function () {
                    $record = $this->getRecord();
                    if ($record) {
                        $record->delete();
                        $this->redirect($this->getResource()::getUrl('index'));
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Format numerical values - remove commas
        $data['min_amount'] = str_replace(',', '', $data['min_amount']);
        $data['fixed_amount'] = str_replace(',', '', $data['fixed_amount']);

        // Ensure the max amount is null when empty (highest bracket)
        if (empty($data['max_amount'])) {
            $data['max_amount'] = null;
        } else {
            $data['max_amount'] = str_replace(',', '', $data['max_amount']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Show success notification
        \Filament\Notifications\Notification::make()
            ->title('Tax Bracket Updated')
            ->body('The PAYE tax bracket has been successfully updated.')
            ->success()
            ->send();
    }
}
