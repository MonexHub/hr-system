<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;


    protected function afterCreate(): void
    {
        $user = User::create([
            'name' => $this->record->full_name,
            'email' => $this->record->email,
            'password' => Hash::make($this->data['password'])
        ]);

        $this->record->update(['user_id' => $user->id]);

        if (isset($this->data['roles'])) {
            $user->syncRoles($this->data['roles']);
        }
    }
}
