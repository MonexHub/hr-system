<?php

namespace App\Filament\Admin\Resources\NotificationPreferenceResource\Pages;

use App\Filament\Admin\Resources\NotificationPreferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotificationPreference extends EditRecord
{
    protected static string $resource = NotificationPreferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
