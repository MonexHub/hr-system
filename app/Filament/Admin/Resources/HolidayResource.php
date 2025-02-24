<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\HolidayResource\Pages;
use App\Filament\Admin\Resources\HolidayResource\RelationManagers;
use App\Models\Holiday;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Name (English)')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('name_sw')
                                    ->label('Name (Swahili)')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ])
                            ->columns(2),

                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->label('Description (English)')
                                    ->maxLength(65535)
                                    ->columnSpan(1),

                                Forms\Components\Textarea::make('description_sw')
                                    ->label('Description (Swahili)')
                                    ->maxLength(65535)
                                    ->columnSpan(1),
                            ])
                            ->columns(2),

                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\DatePicker::make('date')
                                    ->required()
                                    ->format('Y-m-d')
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('is_recurring')
                                    ->label('Recurring Annually')
                                    ->default(true)
                                    ->columnSpan(1),
                            ])
                            ->columns(2),

                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'public' => 'Public Holiday',
                                        'religious' => 'Religious Holiday',
                                        'company' => 'Company Holiday',
                                    ])
                                    ->default('public')
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                    ])
                                    ->default('active')
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columns(2),

                        Forms\Components\Toggle::make('send_notification')
                            ->label('Send Notifications')
                            ->default(true)
                            ->helperText('Send notifications to employees about this holiday'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name (English)')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name_sw')
                    ->label('Name (Swahili)')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_recurring')
                    ->boolean()
                    ->label('Recurring')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'religious' => 'warning',
                        'company' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('send_notification')
                    ->boolean()
                    ->label('Notifications')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'public' => 'Public Holiday',
                        'religious' => 'Religious Holiday',
                        'company' => 'Company Holiday',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                Tables\Filters\TernaryFilter::make('is_recurring')
                    ->label('Recurring'),
                Tables\Filters\TernaryFilter::make('send_notification')
                    ->label('Notifications Enabled'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
