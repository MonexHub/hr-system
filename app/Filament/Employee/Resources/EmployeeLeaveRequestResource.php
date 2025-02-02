<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeLeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeLeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Leave Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Request Details')
                ->description('Create or edit leave request. Available balance will be shown after selecting employee and leave type.')
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
                        ->disabled(fn () => !Auth::user()->hasRole('hr'))
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('leave_type_id', null);
                            $set('remaining_days', null);
                        }),

                    Forms\Components\Select::make('leave_type_id')
                        ->relationship('leaveType', 'name')
                        ->required()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            if (!$state) return;

                            $employeeId = $get('employee_id');
                            if (!$employeeId) return;

                            $leaveBalance = \App\Models\LeaveBalance::where('employee_id', $employeeId)
                                ->where('leave_type_id', $state)
                                ->where('year', now()->year)
                                ->first();

                            $usedDays = LeaveRequest::where('employee_id', $employeeId)
                                ->where('leave_type_id', $state)
                                ->whereYear('start_date', now()->year)
                                ->where('status', 'approved')
                                ->sum('days_taken');

                            // Calculate remaining days based on leave balance
                            $remainingDays = $leaveBalance ? $leaveBalance->days_remaining : 0;
                            $set('remaining_days', $remainingDays);
                            $set('max_days', $leaveBalance?->total_days ?? 0);

                            // Show balance notification
                            \Filament\Notifications\Notification::make()
                                ->info()
                                ->title('Leave Balance')
                                ->body("Available balance: {$remainingDays} days")
                                ->send();
                        }),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('start_date')
                                ->label('From Date')
                                ->required()
                                ->afterOrEqual('today')
                                ->native(false)
                                ->reactive()
                                ->weekStartsOnMonday()
                                ->displayFormat('d/m/Y')
                                ->afterStateUpdated(fn ($state, callable $set, $get) =>
                                static::calculateDays($state, $get('end_date'), $set, $get)),

                            Forms\Components\DatePicker::make('end_date')
                                ->label('To Date')
                                ->required()
                                ->afterOrEqual('start_date')
                                ->native(false)
                                ->reactive()
                                ->weekStartsOnMonday()
                                ->displayFormat('d/m/Y')
                                ->afterStateUpdated(fn ($state, callable $set, $get) =>
                                static::calculateDays($get('start_date'), $state, $set, $get)),
                        ]),

                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('days_taken')
                                ->label('Days Requested')
                                ->disabled()
                                ->numeric()
                                ->suffix('days')
                                ->dehydrated(),

                            Forms\Components\TextInput::make('max_days')
                                ->label('Annual Allowance')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('remaining_days')
                                ->label('Available Balance')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($state, $get) =>
                                max(0, ($state ?? 0) - ($get('days_taken') ?? 0)))
                                ->hint(fn ($state) => $state < 0 ? 'Insufficient balance!' : '')
                                ->hintColor('danger'),
                        ]),
                ])
                ->columns(2),

            Forms\Components\Section::make('Request Information')
                ->schema([
                    Forms\Components\RichEditor::make('reason')
                        ->required()
                        ->label('Leave Reason')
                        ->placeholder('Please provide detailed reason for the leave request')
                        ->toolbarButtons([
                            'bold',
                            'bulletList',
                            'orderedList',
                        ])
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('attachments')
                        ->label('Supporting Documents')
                        ->hint('Max 3 files, 5MB each. Accepted: PDF, Images')
                        ->directory('leave-attachments')
                        ->preserveFilenames()
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

                    Forms\Components\Textarea::make('rejection_reason')
                        ->required(fn (callable $get) => $get('status') === 'rejected')
                        ->visible(fn (callable $get) => $get('status') === 'rejected'),

                    Forms\Components\Textarea::make('notes')
                        ->label('Additional Notes'),
                ])
                ->columns(2),
        ])->columns(1);
    }

    protected static function calculateDays($startDate, $endDate, callable $set, callable $get): void
    {
        if (!$startDate || !$endDate) return;

        $days = ceil(\Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1);
        $set('days_taken', $days);

        $remainingDays = $get('remaining_days') ?? 0;
        if ($days > $remainingDays) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Insufficient Leave Balance')
                ->body("Requested {$days} days exceeds available balance of {$remainingDays} days.")
                ->persistent()
                ->send();
        }
    }

    protected static function validateDates($startDate, $endDate, callable $set, callable $get): void
    {
        if (!$startDate || !$endDate) return;

        $days = ceil(Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);
        $set('days_taken', $days);

        $remainingDays = $get('remaining_days') ?? 0;
        if ($days > $remainingDays) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Insufficient Leave Balance')
                ->body("You have requested {$days} days but only have {$remainingDays} days available.")
                ->persistent()
                ->send();
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Leave Period')
                    ->formatStateUsing(fn (LeaveRequest $record): string =>
                    "{$record->start_date->format('d/m/Y')} - {$record->end_date->format('d/m/Y')}")
                    ->description(fn (LeaveRequest $record): string =>
                        "{$record->days_taken} " . str('day')->plural($record->days_taken))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'pending_hr',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-arrow-path' => 'pending_hr',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-x-mark' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => str($state)
                        ->replace('_', ' ')
                        ->title()),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested On')
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cancel')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Leave Request')
                    ->modalDescription('Are you sure you want to cancel this leave request? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, cancel request')
                    ->visible(fn (LeaveRequest $record): bool =>
                    in_array($record->status, ['pending', 'pending_hr']))
                    ->action(fn (LeaveRequest $record) => $record->cancel()),
            ]);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'My Requests';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('employee_id', Auth::user()->employee->id);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeLeaveRequests::route('/'),
            'create' => Pages\CreateEmployeeLeaveRequest::route('/create'),
            'edit' => Pages\EditEmployeeLeaveRequest::route('/{record}/edit'),
        ];
    }


    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'approve_manager',  // For department managers
            'approve_hr',       // For HR managers
            'reject',
            'create_for_others' // Special permission for HR to create requests for others
        ];
    }
}
