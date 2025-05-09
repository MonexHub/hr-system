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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AttendanceResource extends Resource
{
    protected static ?string $navigationGroup = 'Human Resources';

    protected static ?int $navigationSort = 2;

    protected static ?string $model = Attendance::class;

    // Allow access to all authenticated users
    public static function canAccess(): bool
    {
        return auth()->check();
    }

    // This is the key method that limits what data users can see
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Get current user
        $user = auth()->user();

        // If user has privileged role, show all records
        if ($user->hasAnyRole(['super_admin', 'chief_executive_officer', 'hr_manager'])) {
            return $query;
        }

        // Otherwise, only show records for the current employee
        $employeeId = $user->employee->id ?? null;
        return $query->where('employee_id', $employeeId);
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isPrivilegedUser = $user->hasAnyRole(['super_admin', 'chief_executive_officer', 'hr_manager']);

        return $form
            ->schema([
                Forms\Components\Section::make('Employee Information')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->options(function () use ($isPrivilegedUser, $user) {
                                // For privileged users, show all employees
                                if ($isPrivilegedUser) {
                                    return Employee::query()->pluck('first_name', 'id');
                                }

                                // For regular users, only show themselves
                                $employeeId = $user->employee->id ?? null;
                                return Employee::query()
                                    ->where('id', $employeeId)
                                    ->pluck('first_name', 'id');
                            })
                            ->searchable()
                            ->required()
                            // Disable changing employee for non-privileged users
                            ->disabled(!$isPrivilegedUser)
                            // Default to current employee for non-privileged users
                            ->default(function () use ($isPrivilegedUser, $user) {
                                if (!$isPrivilegedUser) {
                                    return $user->employee->id ?? null;
                                }
                                return null;
                            }),

                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),
                    ]),

                Forms\Components\Section::make('Attendance Details')
                    ->schema([
                        Forms\Components\DateTimePicker::make('check_in')
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('check_out')
                            ->seconds(false)
                            ->afterOrEqual('check_in'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'present' => 'Present',
                                'absent' => 'Absent',
                                'late' => 'Late',
                                'early_departure' => 'Early Departure',
                                'half_day' => 'Half Day',
                                'overtime' => 'Overtime',
                                'weekend' => 'Weekend',
                                'holiday' => 'Holiday',
                            ])
                            ->default('pending'),
                    ]),

                Forms\Components\Section::make('Time Calculations')
                    ->schema([
                        Forms\Components\TextInput::make('total_hours')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),

                        Forms\Components\TextInput::make('standard_hours')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),

                        Forms\Components\TextInput::make('overtime_hours')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),

                        Forms\Components\TextInput::make('late_minutes')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),

                        Forms\Components\TextInput::make('early_out_minutes')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),
                    ]),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isPrivilegedUser = auth()->user()->hasAnyRole(['super_admin', 'chief_executive_officer', 'hr_manager']);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('First Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('employee.last_name')
                    ->label('Last Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('employee.employee_code')
                    ->label('Employee Code')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_in')
                    ->dateTime('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_out')
                    ->dateTime('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_hours')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'absent',
                        'warning' => ['late', 'early_departure', 'half_day'],
                        'success' => ['present', 'overtime'],
                        'secondary' => ['pending', 'weekend', 'holiday'],
                    ]),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from_date'),
                        Forms\Components\DatePicker::make('to_date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'early_departure' => 'Early Departure',
                        'half_day' => 'Half Day',
                        'overtime' => 'Overtime',
                        'weekend' => 'Weekend',
                        'holiday' => 'Holiday',
                    ]),

                // Only show employee filter to privileged users
                SelectFilter::make('employee')
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->preload()
                    ->visible($isPrivilegedUser),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Only show delete action to privileged users
                Tables\Actions\DeleteAction::make()
                    ->visible($isPrivilegedUser),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Only show bulk delete to privileged users
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible($isPrivilegedUser),
                ]),
            ]);
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
        $query = static::getModel()::where('date', today())
            ->where('status', 'pending');

        // For non-privileged users, only count their own records
        if (!auth()->user()->hasAnyRole(['super_admin', 'chief_executive_officer', 'hr_manager'])) {
            $employeeId = auth()->user()->employee->id ?? null;
            $query->where('employee_id', $employeeId);
        }

        return $query->count();
    }
}
