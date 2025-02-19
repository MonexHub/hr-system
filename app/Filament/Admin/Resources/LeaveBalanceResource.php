<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LeaveBalanceResource\Pages;
use App\Models\LeaveBalance;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeaveBalanceResource extends Resource
{
    protected static ?string $model = LeaveBalance::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Leave Management';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Leave Balance';
    protected static ?string $pluralModelLabel = 'Leave Balances';

    protected static function isAdmin(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'hr_manager']);
    }

    public static function form(Form $form): Form
    {
        $isAdmin = static::isAdmin();

        return $form->schema([
            Section::make('Employee Information')
                ->description('Select the employee and leave type')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->relationship(
                            'employee',
                            'first_name',
                            fn ($query) => $query->select(['id', 'first_name', 'last_name'])
                                ->orderBy('first_name')
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->required()
                        ->label('Employee')
                        ->disabled(fn ($record) => $record !== null || !$isAdmin)
                        ->default(fn () => $isAdmin ? null : auth()->user()->employee?->id),

                    Forms\Components\Select::make('leave_type_id')
                        ->relationship('leaveType', 'name', fn ($query) => $query->where('is_active', true))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->label('Leave Type')
                        ->disabled(fn ($record) => $record !== null),

                    Forms\Components\Select::make('year')
                        ->options(fn () => array_combine(
                            range(date('Y') - 1, date('Y') + 1),
                            range(date('Y') - 1, date('Y') + 1)
                        ))
                        ->default(date('Y'))
                        ->required()
                        ->disabled(fn ($record) => $record !== null),
                ]),

            Section::make('Balance Details')
                ->description('Configure the leave balance details')
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('entitled_days')
                                ->label('Entitled Days')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->step(0.5)
                                ->disabled(!$isAdmin),

                            Forms\Components\TextInput::make('carried_forward_days')
                                ->label('Carried Forward')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->step(0.5)
                                ->disabled(!$isAdmin),

                            Forms\Components\TextInput::make('additional_days')
                                ->label('Additional Days')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->step(0.5)
                                ->disabled(!$isAdmin),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('taken_days')
                                ->label('Taken Days')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->disabled()
                                ->step(0.5),

                            Forms\Components\TextInput::make('pending_days')
                                ->label('Pending Days')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->disabled()
                                ->step(0.5),
                        ]),

                    Forms\Components\Textarea::make('remarks')
                        ->maxLength(1000)
                        ->columnSpanFull()
                        ->disabled(!$isAdmin),
                ]),

            Section::make('Balance Summary')
                ->description('Current balance information')
                ->schema([
                    Forms\Components\Placeholder::make('total_entitlement')
                        ->label('Total Entitlement')
                        ->content(fn (LeaveBalance $record) => $record?->total_entitlement ?? 0),

                    Forms\Components\Placeholder::make('available_balance')
                        ->label('Available Balance')
                        ->content(fn (LeaveBalance $record) => $record?->available_balance ?? 0),
                ])
                ->visible(fn ($record) => $record !== null),
        ]);
    }

    public static function table(Table $table): Table
    {
        $isAdmin = static::isAdmin();

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Employee')
                    ->formatStateUsing(fn ($record) => $record->employee->first_name . ' ' . $record->employee->last_name)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('employee', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('employee.first_name', $direction)
                            ->orderBy('employee.last_name', $direction);
                    })
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('entitled_days')
                    ->label('Entitled')
                    ->numeric(
                        decimalPlaces: 1,
                        thousandsSeparator: ',',
                    ),

                Tables\Columns\TextColumn::make('carried_forward_days')
                    ->label('Carried Forward')
                    ->numeric(
                        decimalPlaces: 1,
                        thousandsSeparator: ',',
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make('additional_days')
                    ->label('Additional')
                    ->numeric(
                        decimalPlaces: 1,
                        thousandsSeparator: ',',
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make('taken_days')
                    ->label('Taken')
                    ->numeric(
                        decimalPlaces: 1,
                        thousandsSeparator: ',',
                    ),

                Tables\Columns\TextColumn::make('pending_days')
                    ->label('Pending')
                    ->numeric(
                        decimalPlaces: 1,
                        thousandsSeparator: ',',
                    ),

                Tables\Columns\TextColumn::make('available_balance')
                    ->label('Available')
                    ->numeric(
                        decimalPlaces: 1,
                        thousandsSeparator: ',',
                    )
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw('(entitled_days + carried_forward_days + additional_days - taken_days - pending_days) ' . $direction);
                    }),
            ])
            ->filters([
                SelectFilter::make('employee')
                    ->relationship(
                        'employee',
                        'first_name',
                        fn ($query) => $query->select(['id', 'first_name', 'last_name'])
                            ->orderBy('first_name')
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->preload()
                    ->visible($isAdmin),

                SelectFilter::make('leave_type')
                    ->relationship('leaveType', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('year')
                    ->options(fn () => array_combine(
                        range(date('Y') - 1, date('Y') + 1),
                        range(date('Y') - 1, date('Y') + 1)
                    )),

                Tables\Filters\Filter::make('with_balance')
                    ->query(fn (Builder $query): Builder => $query->withAvailableBalance())
                    ->label('Has Available Balance')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible($isAdmin),
                Tables\Actions\Action::make('adjust')
                    ->icon('heroicon-o-plus-circle')
                    ->visible($isAdmin)
                    ->form([
                        Forms\Components\TextInput::make('days')
                            ->required()
                            ->numeric()
                            ->step(0.5)
                            ->label('Number of Days'),

                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->label('Reason for Adjustment'),
                    ])
                    ->action(function (LeaveBalance $record, array $data): void {
                        $record->additional_days += $data['days'];
                        $record->remarks = ($record->remarks ? $record->remarks . "\n" : '') .
                            "Adjusted by " . auth()->user()->name . ": {$data['days']} days - {$data['reason']}";
                        $record->save();

                        Notification::make()
                            ->title('Balance Adjusted')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['employee', 'leaveType'])
            ->whereHas('employee')
            ->whereHas('leaveType');

        if (!static::isAdmin()) {
            $query->where('employee_id', auth()->user()->employee?->id);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveBalances::route('/'),
            'create' => Pages\CreateLeaveBalance::route('/create'),
            'edit' => Pages\EditLeaveBalance::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return static::isAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return static::isAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return static::isAdmin();
    }
}
