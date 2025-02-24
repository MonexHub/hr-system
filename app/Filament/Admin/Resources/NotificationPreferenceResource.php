<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NotificationPreferenceResource\Pages;
use App\Filament\Admin\Resources\NotificationPreferenceResource\RelationManagers;
use App\Models\Employee;
use App\Models\NotificationPreference;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationPreferenceResource extends Resource
{
    protected static ?string $model = NotificationPreference::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Select::make('employees')
                            ->multiple()
                            ->label('Select Employees')
                            ->options(Employee::query()
                                ->whereDoesntHave('notificationPreferences')
                                ->where('employment_status', 'active')
                                ->pluck('first_name', 'id')
                                ->map(function ($name, $id) {
                                    $employee = Employee::find($id);
                                    return $employee->full_name;
                                }))
                            ->getSearchResultsUsing(fn (string $search): array =>
                            Employee::query()
                                ->whereDoesntHave('notificationPreferences')
                                ->where('employment_status', 'active')
                                ->where(function ($query) use ($search) {
                                    $query->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                })
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($employee) => [$employee->id => $employee->full_name])
                                ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn (string $context): bool => $context === 'create'),

                        Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn (string $context): bool => $context === 'edit'),

                        Select::make('preferred_language')
                            ->options([
                                'en' => 'English',
                                'sw' => 'Swahili',
                            ])
                            ->default('en')
                            ->required(),

                        Forms\Components\Grid::make()
                            ->schema([
                                Toggle::make('holiday_notifications')
                                    ->label('Holiday Notifications')
                                    ->default(true),

                                Toggle::make('birthday_notifications')
                                    ->label('Birthday Notifications')
                                    ->default(true),

                                Toggle::make('email_notifications')
                                    ->label('Email Notifications')
                                    ->default(true),

                                Toggle::make('in_app_notifications')
                                    ->label('In-App Notifications')
                                    ->default(true),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('preferred_language')
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('holiday_notifications')
                    ->boolean()
                    ->label('Holidays'),

                Tables\Columns\IconColumn::make('birthday_notifications')
                    ->boolean()
                    ->label('Birthdays'),

                Tables\Columns\IconColumn::make('email_notifications')
                    ->boolean()
                    ->label('Email'),

                Tables\Columns\IconColumn::make('in_app_notifications')
                    ->boolean()
                    ->label('In-App'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('preferred_language')
                    ->options([
                        'en' => 'English',
                        'sw' => 'Swahili',
                    ]),
                Tables\Filters\TernaryFilter::make('holiday_notifications'),
                Tables\Filters\TernaryFilter::make('birthday_notifications'),
                Tables\Filters\TernaryFilter::make('email_notifications'),
                Tables\Filters\TernaryFilter::make('in_app_notifications'),
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
            'index' => Pages\ListNotificationPreferences::route('/'),
            'create' => Pages\CreateNotificationPreference::route('/create'),
            'edit' => Pages\EditNotificationPreference::route('/{record}/edit'),
        ];
    }
}
