<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AnnouncementResource\Pages;
use App\Filament\Admin\Resources\AnnouncementResource\RelationManagers;
use App\Models\Announcement;
use App\Models\Department;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Announcement Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan('full'),

                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpan('full'),

                        Forms\Components\Select::make('department_id')
                            ->label('Department')
                            ->options(Department::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->helperText('Leave empty for company-wide announcement')
                            ->nullable(),

                        Forms\Components\TextInput::make('icon')
                            ->label('Icon CSS Class')
                            ->helperText('Font Awesome class (e.g., fas fa-users)')
                            ->maxLength(50)
                            ->nullable(),

                        Forms\Components\Toggle::make('is_important')
                            ->label('Mark as Important')
                            ->helperText('Important announcements will be highlighted')
                            ->default(false),
                    ]),
            ])
            ->statePath('data');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->placeholder('Company-wide')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_important')
                    ->label('Important')
                    ->boolean(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name')
                    ->preload()
                    ->searchable()
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_important')
                    ->label('Important Announcements'),

                Tables\Filters\TernaryFilter::make('company_wide')
                    ->label('Company-wide')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('department_id'),
                        false: fn (Builder $query) => $query->whereNotNull('department_id'),
                    ),
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
//            'view' => Pages\ViewAnnouncement::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
