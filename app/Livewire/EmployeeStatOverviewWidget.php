<?php

namespace App\Livewire;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class EmployeeStatOverviewWidget extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // ...
            )
            ->columns([
                // ...
            ]);
    }
}
