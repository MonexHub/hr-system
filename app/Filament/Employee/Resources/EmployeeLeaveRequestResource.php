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
            Forms\Components\Section::make()
                ->description('Submit a new leave request. Your available balance will be shown after selecting leave type.')
                ->schema([
                    Forms\Components\Select::make('leave_type_id')
                        ->relationship('leaveType', 'name')
                        ->required()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (!$state) return;

                            $leaveBalance = \App\Models\LeaveBalance::where('employee_id', Auth::user()->employee->id)
                                ->where('leave_type_id', $state)
                                ->where('year', now()->year)
                                ->first();

                            $set('remaining_days', $leaveBalance?->days_remaining ?? 0);

                            // Show balance notification
                            \Filament\Notifications\Notification::make()
                                ->info()
                                ->title('Leave Balance')
                                ->body("You have {$leaveBalance?->days_remaining} days remaining for this leave type.")
                                ->send();
                        })
                        ->columnSpanFull(),

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
                                self::validateDates($state, $get('end_date'), $set, $get)),

                            Forms\Components\DatePicker::make('end_date')
                                ->label('To Date')
                                ->required()
                                ->afterOrEqual('start_date')
                                ->native(false)
                                ->reactive()
                                ->weekStartsOnMonday()
                                ->displayFormat('d/m/Y')
                                ->afterStateUpdated(fn ($state, callable $set, $get) =>
                                self::validateDates($get('start_date'), $state, $set, $get)),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('days_taken')
                                ->label('Days Requested')
                                ->disabled()
                                ->numeric()
                                ->suffix('days')
                                ->hint(fn ($get) => $get('remaining_days') > 0
                                    ? "Available: {$get('remaining_days')} days"
                                    : "No days available"),

                            Forms\Components\TextInput::make('remaining_days')
                                ->label('Balance After Request')
                                ->disabled()
                                ->numeric()
                                ->suffix('days')
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($state, $get) =>
                                max(0, ($state ?? 0) - ($get('days_taken') ?? 0))),
                        ]),

                    Forms\Components\RichEditor::make('reason')
                        ->required()
                        ->label('Leave Reason')
                        ->placeholder('Please provide detailed reason for your leave request')
                        ->toolbarButtons(['bold', 'bulletList', 'italic'])
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
                ])
                ->columns(2),
        ]);
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

    protected static function calculateDays($startDate, $endDate, callable $set): void
    {
        if ($startDate && $endDate) {
            $days = ceil(Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);
            $set('days_taken', $days);
        }
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeLeaveRequests::route('/'),
            'create' => Pages\CreateEmployeeLeaveRequest::route('/create'),
            'edit' => Pages\EditEmployeeLeaveRequest::route('/{record}/edit'),
        ];
    }
}
