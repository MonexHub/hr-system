<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $user = $this->record->user;

        $userData = [
            'name' => $this->record->full_name,
            'email' => $this->record->email
        ];

        if (!empty($this->data['password'])) {
            $userData['password'] = Hash::make($this->data['password']);
        }

        $user->update($userData);

        if (isset($this->data['roles'])) {
            $user->syncRoles($this->data['roles']);
        }
    }
}
