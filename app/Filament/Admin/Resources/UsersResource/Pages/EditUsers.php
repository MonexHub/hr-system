<?php

namespace App\Filament\Admin\Resources\UsersResource\Pages;

use App\Filament\Admin\Resources\UsersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUsers extends EditRecord
{
    protected static string $resource = UsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Save roles separately
        if (isset($data['roles'])) {
            $this->record->syncRoles($data['roles']);
            unset($data['roles']);
        }

        return $data;
    }
}
