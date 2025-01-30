<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterUpdate(): void
    {
        // Get or create associated user
        $user = $this->record->user;

        if (!$user) {
            // If no user exists, create one
            $user = User::create([
                'name' => $this->record->full_name,
                'email' => $this->record->email,
                'password' => Hash::make(Str::random(16)),
            ]);

            // Link user to employee
            $this->record->update(['user_id' => $user->id]);
        } else {
            // Update existing user
            $userData = [
                'name' => $this->record->full_name,
                'email' => $this->record->email,
            ];

            // Only update password if it was provided
            if (!empty($this->data['password'])) {
                $userData['password'] = Hash::make($this->data['password']);
            }

            $user->update($userData);
        }

        // Sync roles if provided
        if (isset($this->data['roles'])) {
            $user->syncRoles($this->data['roles']);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
