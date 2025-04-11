<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Actions\DeleteAction as FilamentDeleteAction;
use Filament\Tables\Actions\DeleteAction as TablesDeleteAction;

class FilamentActionsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Fix for Filament\Actions\DeleteAction
        if (class_exists(FilamentDeleteAction::class)) {
            FilamentDeleteAction::configureUsing(function (FilamentDeleteAction $action): void {
                $action->modalHeading(fn ($record) => $record ? "Delete {$action->getRecordTitle()}" : "Delete")
                    ->modalDescription(fn ($record) => $record ? "Are you sure you'd like to delete this item?" : "Are you sure you'd like to delete this item?")
                    ->modalSubmitActionLabel('Delete');
            });
        }

        // Fix for Filament\Tables\Actions\DeleteAction
        if (class_exists(TablesDeleteAction::class)) {
            TablesDeleteAction::configureUsing(function (TablesDeleteAction $action): void {
                $action->modalHeading(fn ($record) => $record ? "Delete item" : "Delete")
                    ->modalDescription(fn ($record) => $record ? "Are you sure you'd like to delete this item?" : "Are you sure you'd like to delete this item?")
                    ->modalSubmitActionLabel('Delete');
            });
        }
    }
}
