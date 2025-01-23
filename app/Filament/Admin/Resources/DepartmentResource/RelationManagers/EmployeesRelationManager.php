<?php

namespace App\Filament\Admin\Resources\DepartmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    protected static ?string $recordTitleAttribute = 'full_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('job_title')
                    ->required(),
                Forms\Components\Select::make('employment_status')
                    ->options([
                        'active' => 'Active',
                        'probation' => 'Probation',
                        'terminated' => 'Terminated',
                    ])
                    ->required(),
                Forms\Components\Select::make('contract_type')
                    ->options([
                        'permanent' => 'Permanent',
                        'contract' => 'Contract',
                        'temporary' => 'Temporary',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('appointment_date')
                    ->required(),
                Forms\Components\TextInput::make('salary')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('job_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('employment_status'),
                Tables\Columns\TextColumn::make('contract_type'),
                Tables\Columns\TextColumn::make('appointment_date')
                    ->date(),
                Tables\Columns\TextColumn::make('salary')
                    ->money()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employment_status')
                    ->options([
                        'active' => 'Active',
                        'probation' => 'Probation',
                        'terminated' => 'Terminated',
                    ]),
                Tables\Filters\SelectFilter::make('contract_type')
                    ->options([
                        'permanent' => 'Permanent',
                        'contract' => 'Contract',
                        'temporary' => 'Temporary',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('appointment_date', 'desc');
    }
}
