<?php

namespace App\Filament\Employee\Resources\ProfileResource\Pages;

use App\Filament\Employee\Resources\ProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProfile extends CreateRecord
{
    protected static string $resource = ProfileResource::class;
}
