<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Workforce Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Attendance Details')
                    ->description('Record employee attendance details')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('employee_id')
                                ->label('Employee')
                                ->options(fn () => Employee::query()
                                    ->where('application_status', 'active')
                                    ->orderBy('first_name')
                                    ->get()
                                    ->mapWithKeys(fn ($employee) => [
                                        $employee->id => $employee->first_name . ' ' . $employee->last_name
                                    ])
                                )
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpan(1),

                            Forms\Components\DatePicker::make('date')
                                ->label('Date')
                                ->default(now())
                                ->maxDate(now())
                                ->format('Y-m-d')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    static::calculateAttendanceMetrics($get, $set);
                                })
                                ->columnSpan(1),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TimePicker::make('check_in')
                                ->label('Check-in Time')
                                ->seconds(false)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    static::calculateAttendanceMetrics($get, $set);
                                })
                                ->rule(function (Get $get): ?\Closure {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        if ($get('check_out') && Carbon::parse($value)->isAfter(Carbon::parse($get('check_out')))) {
                                            $fail('Check-in time must be before check-out time.');
                                        }
                                    };
                                })
                                ->columnSpan(1),

                            Forms\Components\TimePicker::make('check_out')
                                ->label('Check-out Time')
                                ->seconds(false)
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    static::calculateAttendanceMetrics($get, $set);
                                })
                                ->rule(function (Get $get): ?\Closure {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        if ($value && Carbon::parse($get('check_in'))->isAfter(Carbon::parse($value))) {
                                            $fail('Check-out time must be after check-in time.');
                                        }
                                    };
                                })
                                ->columnSpan(1),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('total_hours')
                                ->label('Total Hours')
                                ->numeric()
                                ->default(0)
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('overtime_hours')
                                ->label('Overtime Hours')
                                ->numeric()
                                ->default(0)
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options(fn () => config('attendance.status_options', [
                                    'pending' => 'Pending',
                                    'present' => 'Present',
                                    'absent' => 'Absent',
                                    'late' => 'Late',
                                    'half_day' => 'Half Day',
                                    'overtime' => 'Overtime',
                                ]))
                                ->default('pending')
                                ->required()
                                ->disabled()
                                ->dehydrated(),
                        ]),

                        Forms\Components\Textarea::make('notes')
                            ->label('Additional Notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->maxLength(500),
                    ])
                    ->columns(2)
            ]);
    }

    protected static function calculateAttendanceMetrics(Get $get, Set $set): void
    {
        try {
            $date = $get('date');
            $checkIn = $get('check_in');
            $checkOut = $get('check_out');

            // Set default values
            $totalHours = 0;
            $overtimeHours = 0;
            $status = 'pending';

            if ($date && $checkIn) {
                $dateStr = is_string($date) ? $date : Carbon::parse($date)->format('Y-m-d');

                // Convert 12-hour format to 24-hour if needed
                $checkInTime = static::parseTime($dateStr, $checkIn);

                if ($checkOut) {
                    $checkOutTime = static::parseTime($dateStr, $checkOut);

                    // For night shifts, if check-out is before check-in, add a day to check-out
                    if ($checkOutTime->lt($checkInTime)) {
                        $checkOutTime->addDay();
                    }

                    // Calculate total hours
                    $totalHours = $checkOutTime->diffInMinutes($checkInTime) / 60;
                    $totalHours = round($totalHours, 2);

                    // Calculate overtime
                    $standardHours = config('attendance.working_hours.standard_hours', 8);
                    $overtimeHours = max(0, $totalHours - $standardHours);
                    $overtimeHours = round($overtimeHours, 2);

                    // Determine status based on Tanzania work rules
                    $status = static::determineStatus($checkInTime, $totalHours);
                }
            }

            $set('total_hours', abs($totalHours)); // Ensure positive value
            $set('overtime_hours', $overtimeHours);
            $set('status', $status);

        } catch (\Exception $e) {
            \Log::error('Attendance calculation error: ' . $e->getMessage());
            $set('total_hours', 0);
            $set('overtime_hours', 0);
            $set('status', 'pending');
        }
    }

    protected static function parseTime(string $dateStr, string $timeStr): Carbon
    {
        // Clean the time string
        $timeStr = strtolower(trim($timeStr));

        // Convert to 24-hour format if in 12-hour format
        if (strpos($timeStr, 'pm') !== false || strpos($timeStr, 'am') !== false) {
            $time = Carbon::parse($timeStr)->format('H:i');
        } else {
            $time = $timeStr;
        }

        return Carbon::parse($dateStr . ' ' . $time);
    }

    protected static function determineStatus(Carbon $checkInTime, float $totalHours): string
    {
        // Get configuration values
        $absentThreshold = config('attendance.calculation_rules.absent_threshold', 4);
        $halfDayThreshold = config('attendance.calculation_rules.half_day_threshold', 6);
        $standardHours = config('attendance.working_hours.standard_hours', 8);

        // Get work start time for the same day
        $workStartStr = config('attendance.working_hours.start_time', '08:30:00');
        $workStart = Carbon::parse($checkInTime->format('Y-m-d') . ' ' . $workStartStr);
        $graceMinutes = config('attendance.calculation_rules.late_grace_period', 30);
        $lateThreshold = $workStart->copy()->addMinutes($graceMinutes);

        // Determine status
        if ($totalHours < $absentThreshold) {
            return 'absent';
        } elseif ($totalHours < $halfDayThreshold) {
            return 'half_day';
        } elseif ($checkInTime->gt($lateThreshold)) {
            return 'late';
        } elseif ($totalHours > $standardHours) {
            return 'overtime';
        }

        return 'present';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_in')
                    ->label('Check-in')
                    ->dateTime('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_out')
                    ->label('Check-out')
                    ->dateTime('H:i')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('total_hours')
                    ->label('Hours')
                    ->numeric(2)
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('overtime_hours')
                    ->label('Overtime')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable()
                    ->alignment('right'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'absent',
                        'warning' => ['late', 'half_day'],
                        'success' => ['present', 'overtime'],
                        'secondary' => 'pending',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options(fn () => config('attendance.status_options')),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('from')
                                    ->label('From Date'),
                                Forms\Components\DatePicker::make('to')
                                    ->label('To Date'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date)
                            )
                            ->when(
                                $data['to'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->beforeFormFilled(function (Attendance $record): void {
                        // Ensure dates are in the correct format for the form
                        $record->check_in = $record->check_in?->format('H:i');
                        $record->check_out = $record->check_out?->format('H:i');
                    })
                    ->using(function (Attendance $record, array $data): Attendance {
                        // Format the date/time data correctly
                        $dateStr = Carbon::parse($data['date'])->format('Y-m-d');

                        if ($data['check_in']) {
                            $data['check_in'] = Carbon::parse($dateStr . ' ' . $data['check_in']);
                        }

                        if ($data['check_out']) {
                            $checkOut = Carbon::parse($dateStr . ' ' . $data['check_out']);
                            if ($checkOut->lt($data['check_in'])) {
                                $checkOut->addDay();
                            }
                            $data['check_out'] = $checkOut;
                        }

                        $record->update($data);
                        return $record;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->striped()
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('date', today())
            ->where('status', 'pending')
            ->count();
    }
}
