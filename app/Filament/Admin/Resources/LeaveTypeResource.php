<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LeaveTypeResource\Pages;
use App\Filament\Admin\Resources\LeaveTypeResource\RelationManagers;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Collection;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Leave Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Basic Information')
                ->description('Enter the basic details of the leave type')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->label('Leave Type Name'),

                    Forms\Components\Textarea::make('description')
                        ->maxLength(1000)
                        ->columnSpanFull(),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('is_active')
                                ->label('Active Status')
                                ->default(true)
                                ->helperText('Inactive leave types cannot be selected in new requests'),

                            Forms\Components\Toggle::make('is_paid')
                                ->label('Paid Leave')
                                ->default(true)
                                ->helperText('Determines if this leave type is paid or unpaid'),
                        ]),
                ]),

            Section::make('Leave Configuration')
                ->description('Configure the rules and requirements for this leave type')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('min_days_before_request')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->label('Minimum Days Before Request')
                                ->helperText('Minimum number of days before the leave start date that the request must be submitted'),

                            Forms\Components\TextInput::make('max_days_per_request')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(30)
                                ->label('Maximum Days Per Request')
                                ->helperText('Maximum number of days allowed in a single request'),

                            Forms\Components\TextInput::make('max_days_per_year')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(30)
                                ->label('Maximum Days Per Year')
                                ->helperText('Maximum number of days allowed per year'),

                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Toggle::make('requires_attachment')
                                        ->label('Require Attachment')
                                        ->default(false)
                                        ->helperText('Require supporting documents with leave requests'),

                                    Forms\Components\Toggle::make('requires_ceo_approval')
                                        ->label('Require CEO Approval')
                                        ->default(false)
                                        ->helperText('Require CEO approval in addition to department head and HR'),
                                ])->columnSpan(2),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Leave Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_days_per_year')
                    ->label('Annual Limit')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Paid Leave')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('requires_attachment')
                    ->label('Attachment Required')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('requires_ceo_approval')
                    ->label('CEO Approval')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All Statuses')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),

                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Payment Status')
                    ->placeholder('All Types')
                    ->trueLabel('Paid Only')
                    ->falseLabel('Unpaid Only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (LeaveType $record) {
                        if ($record->leaveRequests()->exists()) {
                            return false;
                        }
                    })
                    ->after(function (LeaveType $record) {
                        activity()
                            ->performedOn($record)
                            ->event('deleted')
                            ->log('Leave type deleted');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->leaveRequests()->exists()) {
                                    return false;
                                }
                            }
                        }),
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
            'index' => Pages\ListLeaveTypes::route('/'),
            'create' => Pages\CreateLeaveType::route('/create'),
            'edit' => Pages\EditLeaveType::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
