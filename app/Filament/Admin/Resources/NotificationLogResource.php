<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NotificationLogResource\Pages;
use App\Filament\Admin\Resources\NotificationLogResource\RelationManagers;
use App\Models\NotificationLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationLogResource extends Resource
{
    protected static ?string $model = NotificationLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('employee.full_name')
                            ->label('Employee')
                            ->disabled(),

                        Forms\Components\TextInput::make('type')
                            ->disabled(),

                        Forms\Components\TextInput::make('title')
                            ->disabled(),

                        Forms\Components\Textarea::make('content')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('status')
                            ->disabled(),

                        Forms\Components\Textarea::make('error_message')
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->status === 'failed'),

                        Forms\Components\DateTimePicker::make('sent_at')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('type_icon')
                    ->label('Type')
                    ->icon(fn (NotificationLog $record): string => $record->type_icon),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (NotificationLog $record): string => $record->status_color),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'holiday' => 'Holiday',
                        'birthday' => 'Birthday',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])  // No bulk actions needed for logs
            ->poll('60s');     // Auto-refresh every 60 seconds
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
            'index' => Pages\ListNotificationLogs::route('/'),
//            'create' => Pages\CreateNotificationLog::route('/create'),
            'edit' => Pages\EditNotificationLog::route('/{record}/edit'),
        ];
    }



    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('created_at', '>=', now()->subDay())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->exists()
            ? 'danger'
            : 'success';
    }

    public static function canCreate(): bool
    {
        return false; // Disable creation of logs manually
    }

    public static function canDelete(Model $record): bool
    {
        return false; // Disable deletion of logs
    }

    public static function canDeleteAny(): bool
    {
        return false; // Disable bulk deletion
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Disable editing
    }
}
