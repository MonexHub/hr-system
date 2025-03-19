<?php

namespace App\Filament\Admin\Resources\AttendanceResource\Pages;

use App\Filament\Admin\Resources\AttendanceResource;
use App\Filament\Widgets\EmployeeAttendanceSummaryWidget;
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
        return[
            EmployeeAttendanceSummaryWidget::class
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Create single attendance record
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus'),

            // Bulk Check-in Action
            Actions\Action::make('bulk_check_in')
                ->label('Bulk Check-in')
                ->color('success')
                ->icon('heroicon-o-clock')
                ->form([
                    Grid::make(2)->schema([
                        DatePicker::make('check_in_date')
                            ->label('Check-in Date')
                            ->default(now())
                            ->required()
                            ->maxDate(now())
                            ->closeOnDateSelection(),

                        TimePicker::make('check_in_time')
                            ->label('Check-in Time')
                            ->default(now()->format('H:i'))
                            ->required()
                            ->seconds(false),
                    ]),

                    Select::make('department_id')
                        ->label('Department')
                        ->options(Department::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn (Select $component) => $component
                            ->getContainer()
                            ->getComponent('employee_ids')
                            ->setState([])),

                    MultiSelect::make('employee_ids')
                        ->label('Select Employees')
                        ->options(function (Get $get) {
                            return Employee::query()
                                ->when(
                                    $get('department_id'),
                                    fn (Builder $query, $department) => $query->where('department_id', $department)
                                )
                                ->where('employment_status', 'active')
                                ->orderBy('first_name')
                                ->get()
                                ->mapWithKeys(fn ($employee) => [
                                    $employee->id => $employee->first_name . ' ' . $employee->last_name
                                ]);
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Select employees to check-in')
                ])
                ->action(function (array $data, AttendanceService $attendanceService) {
                    try {
                        // Validate check-in time against configuration
                        $checkInDateTime = Carbon::parse($data['check_in_date'] . ' ' . $data['check_in_time']);
                        $this->validateCheckInTime($checkInDateTime);

                        // Perform bulk check-in
                        $results = $attendanceService->bulkCheckIn(
                            $data['employee_ids'],
                            $checkInDateTime
                        );

                        // Process results
                        $this->processBulkCheckInResults($results);

                    } catch (\Exception $e) {
                        $this->handleActionError('Bulk Check-in Failed', $e);
                    }
                }),

            // Mark Absent Action
            Actions\Action::make('mark_absent')
                ->label('Mark Absent')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->form([
                    Grid::make(2)->schema([
                        DatePicker::make('absent_date')
                            ->label('Date')
                            ->default(now())
                            ->required()
                            ->maxDate(now())
                            ->closeOnDateSelection(),

                        Select::make('department_id')
                            ->label('Department')
                            ->options(Department::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Toggle::make('exclude_leave')
                            ->label('Exclude Employees on Leave')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
                ])
                ->action(function (array $data, AttendanceService $attendanceService) {
                    try {
                        $absentEmployeeIds = $attendanceService->markAbsentEmployees(
                            $data['absent_date'],
                            $data['department_id'] ?? null,
                            $data['exclude_leave'] ?? true
                        );

                        $this->processAbsentResults($absentEmployeeIds);

                    } catch (\Exception $e) {
                        $this->handleActionError('Mark Absent Failed', $e);
                    }
                }),

            // Generate Report Action
            Actions\Action::make('generate_report')
                ->label('Generate Report')
                ->color('primary')
                ->icon('heroicon-o-document-chart-bar')
                ->form([
                    Grid::make(2)->schema([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->maxDate(now())
                            ->closeOnDateSelection(),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->maxDate(now())
                            ->closeOnDateSelection(),

                        Select::make('department_id')
                            ->label('Department')
                            ->options(Department::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('report_type')
                            ->label('Report Type')
                            ->options([
                                'summary' => 'Summary Report',
                                'detailed' => 'Detailed Report',
                                'overtime' => 'Overtime Report',
                                'late' => 'Late Arrivals Report',
                            ])
                            ->required()
                            ->default('summary'),
                    ]),
                ])
                ->action(function (array $data, AttendanceService $attendanceService) {
                    try {
                        $report = $attendanceService->generateAttendanceReport(
                            $data['start_date'],
                            $data['end_date'],
                            $data['department_id'] ?? null,
                            $data['report_type']
                        );

                        // Convert report data to CSV format
                        $csv = fopen('php://temp', 'r+');

                        // Add headers
                        fputcsv($csv, array_keys($report[0] ?? []));

                        // Add data rows
                        foreach ($report as $row) {
                            fputcsv($csv, $row);
                        }

                        rewind($csv);
                        $csvContent = stream_get_contents($csv);
                        fclose($csv);

                        // Create filename with date
                        $filename = sprintf(
                            'attendance_report_%s_to_%s.csv',
                            Carbon::parse($data['start_date'])->format('Y-m-d'),
                            Carbon::parse($data['end_date'])->format('Y-m-d')
                        );

                        return response()->streamDownload(
                            fn () => print($csvContent),
                            $filename,
                            ['Content-Type' => 'text/csv']
                        );

                    } catch (\Exception $e) {
                        $this->handleActionError('Report Generation Failed', $e);
                    }
                }),
        ];
    }

    protected function validateCheckInTime(Carbon $checkInTime): void
    {
        $maxEarlyMinutes = config('attendance.working_hours.check_in_window.max_early', 120);
        $maxLateMinutes = config('attendance.working_hours.check_in_window.max_late', 180);

        $workStartTime = Carbon::parse(config('attendance.working_hours.start_time'));
        $earliestAllowed = $workStartTime->copy()->subMinutes($maxEarlyMinutes);
        $latestAllowed = $workStartTime->copy()->addMinutes($maxLateMinutes);

        if ($checkInTime->lt($earliestAllowed) || $checkInTime->gt($latestAllowed)) {
            throw new \Exception("Check-in time must be between {$earliestAllowed->format('H:i')} and {$latestAllowed->format('H:i')}");
        }
    }

    protected function processBulkCheckInResults(array $results): void
    {
        $successCount = collect($results)->where('status', 'success')->count();
        $errorCount = collect($results)->where('status', 'error')->count();

        $status = $errorCount > 0 ? 'warning' : 'success';
        $message = "Successfully checked in {$successCount} employees.";

        if ($errorCount > 0) {
            $message .= " Failed to check in {$errorCount} employees.";
        }

        Notification::make()
            ->title('Bulk Check-in Results')
            ->body($message)
            ->status($status)
            ->send();

        Log::info('Bulk Check-in Results', $results);
    }

    protected function processAbsentResults(array $absentEmployeeIds): void
    {
        $count = count($absentEmployeeIds);

        Notification::make()
            ->title('Absent Employees Marked')
            ->body("{$count} employees marked as absent.")
            ->status($count > 0 ? 'warning' : 'success')
            ->send();

        Log::info('Mark Absent Results', [
            'count' => $count,
            'employee_ids' => $absentEmployeeIds
        ]);
    }

    protected function handleActionError(string $title, \Exception $exception): void
    {
        Notification::make()
            ->title($title)
            ->body($exception->getMessage())
            ->status('danger')
            ->send();

        Log::error($title, [
            'exception' => $exception,
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
