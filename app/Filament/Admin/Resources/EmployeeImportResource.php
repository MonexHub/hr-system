<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmployeeImportResource\Pages;
use App\Filament\Admin\Resources\EmployeeImportResource\RelationManagers;
use App\Models\EmployeeImport;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeImportResource extends Resource
{
    protected static ?string $model = EmployeeImport::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Employee Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Employee Information')
                    ->schema([
                        Forms\Components\TextInput::make('employee_code')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('middle_name')
                            ->maxLength(255),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                        Forms\Components\DatePicker::make('birthdate'),
                    ])->columns(2),

                Section::make('Employment Details')
                    ->schema([
                        Forms\Components\Select::make('contract_type')
                            ->options([
                                'permanent' => 'Permanent',
                                'contract' => 'Contract',
                            ]),
                        Forms\Components\DatePicker::make('appointment_date'),
                        Forms\Components\TextInput::make('job_title')
                            ->required(),
                        Forms\Components\TextInput::make('branch'),
                        Forms\Components\TextInput::make('department')
                            ->required(),
                        Forms\Components\TextInput::make('salary')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                    ])->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),

                Section::make('Import Status')
                    ->schema([
                        Forms\Components\Select::make('import_status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'processed' => 'Processed',
                                'failed' => 'Failed',
                            ])
                            ->disabled(),
                        Forms\Components\Textarea::make('import_errors')
                            ->disabled()
                            ->visible(fn ($record) => $record?->import_status === 'failed'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('import_status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'processed',
                        'danger' => 'failed',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListEmployeeImports::route('/'),
            'create' => Pages\CreateEmployeeImport::route('/create'),
            'edit' => Pages\EditEmployeeImport::route('/{record}/edit'),
        ];
    }
}
