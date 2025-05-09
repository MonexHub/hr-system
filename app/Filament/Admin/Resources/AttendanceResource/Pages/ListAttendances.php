<?php

namespace App\Filament\Admin\Resources\AttendanceResource\Pages;

use App\Filament\Admin\Resources\AttendanceResource;
use App\Filament\Widgets\EmployeeAttendanceSummaryWidget;
use App\Jobs\FetchAttendanceJob;
use App\Models\Employee;
use App\Models\Department;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeAttendanceSummaryWidget::class
        ];
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('sync')
                ->label('Sync Attendance')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $job = new FetchAttendanceJob(
                        now()->subDays(30)->format('Y-m-d'),
                        now()->format('Y-m-d')
                    );

                    dispatch($job);

                    Notification::make()
                        ->title('Sync started')
                        ->body('Today\'s attendance data is being synced in the background.')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('syncToday')
                ->label('Sync Today\'s Attendance')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $job = new FetchAttendanceJob(
                        now()->format('Y-m-d'),
                        now()->format('Y-m-d')
                    );

                    dispatch($job);

                    Notification::make()
                        ->title('Sync started')
                        ->body('Today\'s attendance data is being synced in the background.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
