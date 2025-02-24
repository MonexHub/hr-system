<?php

namespace App\Filament\Admin\Resources\NotificationLogResource\Pages;

use App\Filament\Admin\Resources\NotificationLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotificationLog extends EditRecord
{
    protected static string $resource = NotificationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
