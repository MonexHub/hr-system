<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Leave Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Request Details')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->relationship(
                            'employee',
                            'first_name',
                            fn (Builder $query) => Auth::user()->hasRole('hr')
                                ? $query
                                : $query->where('reporting_to', Auth::user()->employee?->id)
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled(fn () => !Auth::user()->hasRole('hr')),

                    Forms\Components\Select::make('leave_type_id')
                        ->relationship('leaveType', 'name')
                        ->required()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            $leaveType = \App\Models\LeaveType::find($state);
                            $set('max_days', $leaveType?->max_days ?? 0);

                            // Check remaining balance
                            if ($leaveType && $get('employee_id')) {
                                // Get the leave balance for this employee, leave type, and current year
                                $leaveBalance = \App\Models\LeaveBalance::where('employee_id', $get('employee_id'))
                                    ->where('leave_type_id', $state)
                                    ->where('year', now()->year)
                                    ->first();

                                $usedDays = LeaveRequest::where('employee_id', $get('employee_id'))
                                    ->where('leave_type_id', $state)
                                    ->whereYear('start_date', now()->year)
                                    ->where('status', 'approved')
                                    ->sum('days_taken');

                                // Calculate remaining days based on leave balance
                                $remainingDays = $leaveBalance ? $leaveBalance->days_remaining : 0;
                                $set('remaining_days', $remainingDays);
                            }
                        }),


                    Forms\Components\DatePicker::make('start_date')
                        ->required()
                        ->afterOrEqual('today')
                        ->native(false)
                        ->reactive()
                        ->closeOnDateSelection()
                        ->afterStateUpdated(fn ($state, callable $set, $get) =>
                        static::calculateDays($state, $get('end_date'), $set)),

                    Forms\Components\DatePicker::make('end_date')
                        ->required()
                        ->afterOrEqual('start_date')
                        ->native(false)
                        ->reactive()
                        ->closeOnDateSelection()
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            static::calculateDays($get('start_date'), $state, $set);

                            // Validate leave balance
                            $leaveTypeId = $get('leave_type_id');
                            $employeeId = $get('employee_id');
                            $startDate = \Carbon\Carbon::parse($get('start_date'));
                            $daysTaken = ceil($startDate->diffInDays(\Carbon\Carbon::parse($state)) + 1);

                            if ($leaveTypeId && $employeeId) {
                                $leaveBalance = \App\Models\LeaveBalance::where('employee_id', $employeeId)
                                    ->where('leave_type_id', $leaveTypeId)
                                    ->where('year', $startDate->year)
                                    ->first();

                                if ($leaveBalance && $daysTaken > $leaveBalance->days_remaining) {
                                    // Instead of throwing an exception, use Filament notification
                                    \Filament\Notifications\Notification::make()
                                        ->title('Insufficient Leave Balance')
                                        ->body("Your requested leave of {$daysTaken} days exceeds your available balance of {$leaveBalance->days_remaining} days.")
                                        ->warning()
                                        ->send();

                                    // Reset days taken to remaining balance or 0
                                    $set('days_taken', min($daysTaken, $leaveBalance->days_remaining));
                                }
                            }
                        }),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('days_taken')
                            ->numeric()
                            ->disabled()
                            ->label('Days Requested')
                            ->dehydrated(),

                        Forms\Components\TextInput::make('max_days')
                            ->label('Annual Allowance')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('remaining_days')
                            ->label('Remaining Days')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->hint(fn ($state) => $state < 0 ? 'Exceeds annual allowance!' : '')
                            ->hintColor('danger'),
                    ]),
                ])
                ->columns(2),

            Forms\Components\Section::make('Request Information')
                ->schema([
                    Forms\Components\RichEditor::make('reason')
                        ->required()
                        ->toolbarButtons([
                            'bold',
                            'bulletList',
                            'orderedList',
                        ])
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('attachments')
                        ->directory('leave-attachments')
                        ->multiple()
                        ->maxFiles(3)
                        ->maxSize(5120)
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->columnSpanFull(),

                    Forms\Components\Select::make('status')
                        ->options(function () {
                            if (Auth::user()->hasRole('hr')) {
                                return [
                                    'pending' => 'Pending',
                                    'pending_hr' => 'Pending HR',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                    'cancelled' => 'Cancelled',
                                ];
                            }
                            return [
                                'pending' => 'Pending',
                                'pending_hr' => 'Pending HR',
                                'rejected' => 'Rejected',
                                'cancelled' => 'Cancelled',
                            ];
                        })
                        ->default('pending')
                        ->disabled(fn (string $context): bool => $context === 'create')
                        ->required(),

                    Textarea::make('rejection_reason')
                        ->required(fn (callable $get) => $get('status') === 'rejected')
                        ->visible(fn (callable $get) => $get('status') === 'rejected'),

                  Textarea::make('notes')
                        ->label('Additional Notes'),
                ])
                ->columns(2),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->description(fn (LeaveRequest $record): string => $record->employee->department->name),

                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Period')
                    ->formatStateUsing(fn (LeaveRequest $record): string =>
                        $record->start_date->format('d/m/Y') . ' - ' . $record->end_date->format('d/m/Y'))
                    ->description(fn (LeaveRequest $record): string =>
                    "{$record->days_taken} days")
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'pending_hr' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('managerApprover.name')
                    ->label('Manager Approval')
                    ->description(fn (LeaveRequest $record) =>
                    $record->manager_approved_at?->format('d/m/Y H:i')),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('HR Approval')
                    ->description(fn (LeaveRequest $record) =>
                    $record->approved_at?->format('d/m/Y H:i')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'pending' => 'Pending',
                        'pending_hr' => 'Pending HR',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->relationship('leaveType', 'name')
                    ->label('Leave Type'),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from_date'),
                        Forms\Components\DatePicker::make('to_date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder =>
                                $query->where('start_date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder =>
                                $query->where('end_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Manager Actions
                Tables\Actions\Action::make('manager_approve')
                    ->icon('heroicon-o-check')
                    ->color(Color::Green)
                    ->requiresConfirmation()
                    ->action(fn (LeaveRequest $record) => $record->approveByManager(Auth::id()))
                    ->visible(fn (LeaveRequest $record): bool =>
                        $record->status === 'pending' &&
                        $record->employee->reporting_to === Auth::user()->employee?->id)
                    ->modalHeading('Approve as Manager'),

                // HR Actions
                Tables\Actions\Action::make('hr_approve')
                    ->icon('heroicon-o-check-circle')
                    ->color(Color::Green)
                    ->requiresConfirmation()
                    ->action(fn (LeaveRequest $record) => $record->approveByHR(Auth::id()))
                    ->visible(fn (LeaveRequest $record): bool =>
                        $record->status === 'pending_hr' && Auth::user()->hasRole('hr'))
                    ->modalHeading('Approve as HR'),

                // Reject Action
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color(Color::Red)
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->label('Reason for Rejection'),
                    ])
                    ->action(function (LeaveRequest $record, array $data): void {
                        $record->reject(Auth::id(), $data['rejection_reason']);
                    })
                    ->visible(fn (LeaveRequest $record): bool =>
                        $record->status === 'pending' ||
                        ($record->status === 'pending_hr' && Auth::user()->hasRole('hr'))),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    // Bulk Manager Approval
                    Tables\Actions\BulkAction::make('manager_approve_selected')
                        ->label('Approve as Manager')
                        ->icon('heroicon-o-check')
                        ->color(Color::Green)
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn ($record) =>
                        $record->approveByManager(Auth::id())))
                        ->visible(fn () => !Auth::user()->hasRole('hr'))
                        ->deselectRecordsAfterCompletion(),

                    // Bulk HR Approval
                    Tables\Actions\BulkAction::make('hr_approve_selected')
                        ->label('Approve as HR')
                        ->icon('heroicon-o-check-circle')
                        ->color(Color::Green)
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn ($record) =>
                        $record->approveByHR(Auth::id())))
                        ->visible(fn () => Auth::user()->hasRole('hr'))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    protected static function calculateDays($startDate, $endDate, callable $set): void
    {
        if ($startDate && $endDate) {
            $days = ceil(\Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1);
            $set('days_taken', $days);
        }
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'view' => Pages\ViewLeaveRequest::route('/{record}'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where(function ($query) {
            if (Auth::user()->hasRole('hr')) {
                $query->where('status', 'pending_hr');
            } else {
                $query->where('status', 'pending')
                    ->whereHas('employee', function ($query) {
                        $query->where('reporting_to', Auth::user()->employee?->id);
                    });
            }
        })->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }



}
