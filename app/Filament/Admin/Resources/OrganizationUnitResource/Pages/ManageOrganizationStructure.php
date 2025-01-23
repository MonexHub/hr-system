<?php

namespace App\Filament\Admin\Resources\OrganizationUnitResource\Pages;

use App\Filament\Admin\Resources\OrganizationUnitResource;
use Filament\Resources\Pages\ManageRecords;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageOrganizationStructure extends ListRecords
{
    protected static string $resource = OrganizationUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->modalWidth('xl'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Units')
                ->badge(static::getResource()::getModel()::count()),

            'divisions' => Tab::make('Divisions')
                ->badge(static::getResource()::getModel()::where('unit_type', 'division')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('unit_type', 'division')),

            'departments' => Tab::make('Departments')
                ->badge(static::getResource()::getModel()::where('unit_type', 'department')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('unit_type', 'department')),

            'teams' => Tab::make('Teams')
                ->badge(static::getResource()::getModel()::where('unit_type', 'team')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('unit_type', 'team')),
        ];
    }

}
