<?php

namespace App\Filament\Admin\Resources\LeaveBalanceResource\Pages;

use App\Filament\Admin\Resources\LeaveBalanceResource;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLeaveBalances extends ListRecords
{
    protected static string $resource = LeaveBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->with(['employee', 'leaveType'])
            ->latest();
    }

    public function getTabs(): array
    {
        return [
            'all' => ListRecords\Tab::make()
                ->label('All Balances')
                ->badge(LeaveBalance::count()),

            'with_remaining' => ListRecords\Tab::make()
                ->label('With Remaining Days')
                ->badge(LeaveBalance::where('days_remaining', '>', 0)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('days_remaining', '>', 0)),

            'exhausted' => ListRecords\Tab::make()
                ->label('Exhausted')
                ->badge(LeaveBalance::where('days_remaining', 0)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('days_remaining', 0)),
        ];
    }
}
