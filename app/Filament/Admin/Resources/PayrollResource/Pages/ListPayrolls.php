<?php

namespace App\Filament\Admin\Resources\PayrollResource\Pages;

use App\Filament\Admin\Resources\PayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Only show Create action to users with appropriate permissions
        if (Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])) {
            $actions[] = Actions\CreateAction::make();
        }

        // Only show Generate Payroll action to users with appropriate permissions
        if (Auth::user()->hasAnyRole(['super_admin', 'financial_personnel'])) {
            $actions[] = Actions\Action::make('generate_payroll')
                ->label('Generate Payroll')
                ->icon('heroicon-o-cog')
                ->url('/admin/payrolls/generate');
        }

        return $actions;
    }
}
