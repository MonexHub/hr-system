<?php

namespace App\Filament\Admin\Resources\AttendanceResource\Pages;

use App\Filament\Admin\Resources\AttendanceResource;
use App\Jobs\FetchAttendanceJob;
use App\Models\Department;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Carbon;

class SyncAttendance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = AttendanceResource::class;

    protected static string $view = 'filament.admin.resources.attendance-resource.pages.sync-attendance';

    protected static ?string $title = 'Sync Attendance Data';

    public static ?string $slug = 'attendances/sync';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'departments' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Sync Attendance Data')
                    ->description('Fetch attendance data from ZKBiotime API for a specific date range.')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->afterOrEqual('start_date'),

                        Select::make('departments')
                            ->label('Departments')
                            ->options(Department::all()->pluck('name', 'id'))
                            ->multiple()
                            ->placeholder('All Departments')
                            ->helperText('Leave empty to sync all departments.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function sync(): void
    {
        try {
            $data = $this->form->getState();

            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $departments = null;

            if (!empty($data['departments'])) {
                $departments = implode(',', $data['departments']);
            }

            // Calculate date range and make sure it's not too large
            $dateFrom = Carbon::parse($startDate);
            $dateTo = Carbon::parse($endDate);
            $daysDifference = $dateFrom->diffInDays($dateTo);

            if ($daysDifference > 31) {
                throw new Halt('Date range too large. Please select a range of 31 days or less.');
            }

            // Dispatch job to fetch attendance data
            FetchAttendanceJob::dispatch($startDate, $endDate, $departments);

            Notification::make()
                ->title('Sync started')
                ->body("Attendance data from {$startDate} to {$endDate} is being synced in the background.")
                ->success()
                ->send();
        } catch (Halt $exception) {
            Notification::make()
                ->title('Error')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }
    }

    public function getFormActions(): array
    {
        return [
            \Filament\Forms\Components\Actions\Action::make('sync')
                ->label('Start Sync')
                ->submit('sync'),
        ];
    }
}
