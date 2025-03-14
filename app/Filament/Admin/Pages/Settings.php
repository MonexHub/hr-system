<?php

namespace App\Filament\Admin\Pages;

use App\Livewire\ManageSettings;
use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.admin.pages.settings';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }
}
