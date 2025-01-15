<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\LeaveRequestsRelationManager;
use App\Filament\Admin\Resources\LeaveBalanceResource\Pages;
use App\Models\LeaveBalance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LeaveBalanceResource extends Resource
{
    protected static ?string $model = LeaveBalance::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Leave Management';
    protected static ?string $navigationLabel = 'Leave Balances';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Leave Balance Details')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                            ->searchable()
                            ->preload()
                            ->required() // Ensure this is set to required
                            ->label('Employee'),

                        Forms\Components\Select::make('leave_type_id')
                            ->relationship('leaveType', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Leave Type'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('total_days')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->label('Total Leave Days'),

                                Forms\Components\TextInput::make('days_taken')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->label('Days Used'),

                                Forms\Components\TextInput::make('days_remaining')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->label('Remaining Days'),
                            ]),

                        Forms\Components\TextInput::make('year')
                            ->required()
                            ->numeric()
                            ->default(date('Y'))
                            ->minValue(2000)
                            ->maxValue(2099)
                            ->label('Applicable Year'),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee')
                    ->label('Employee')
                    ->formatStateUsing(fn ($record) => $record->employee->first_name . ' ' . $record->employee->last_name)
                    ->searchable(['employee.first_name', 'employee.last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_days')
                    ->label('Total Days')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_taken')
                    ->label('Days Used')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Remaining Days')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('year')
                    ->label('Year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name')
                    ->label('Filter by Employee')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name . ' ' . $record->last_name),

                Tables\Filters\SelectFilter::make('leave_type')
                    ->relationship('leaveType', 'name')
                    ->label('Filter by Leave Type'),

                Tables\Filters\SelectFilter::make('year')
                    ->options(array_combine(
                        range(date('Y')-2, date('Y')+2),
                        range(date('Y')-2, date('Y')+2)
                    ))
                    ->label('Filter by Year'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
         LeaveRequestsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveBalances::route('/'),
            'create' => Pages\CreateLeaveBalance::route('/create'),
            'edit' => Pages\EditLeaveBalance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
