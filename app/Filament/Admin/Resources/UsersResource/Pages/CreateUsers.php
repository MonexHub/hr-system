<?php

namespace App\Filament\Admin\Resources\UsersResource\Pages;

use App\Filament\Admin\Resources\UsersResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUsers extends CreateRecord
{
    protected static string $resource = UsersResource::class;
}
