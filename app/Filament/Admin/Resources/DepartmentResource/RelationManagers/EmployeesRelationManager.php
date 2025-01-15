<?php

namespace App\Filament\Admin\Resources\DepartmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static ?string $title = 'Department Employees';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Employee Information')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('first_name')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('last_name')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),

                            Forms\Components\DatePicker::make('appointment_date')
                                ->required()
                                ->default(now()),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('employment_status')
                                ->options([
                                    'active' => 'Active',
                                    'probation' => 'Probation',
                                    'suspended' => 'Suspended',
                                    'terminated' => 'Terminated',
                                ])
                                ->default('active')
                                ->required(),

                            Forms\Components\Select::make('contract_type')
                                ->options([
                                    'permanent' => 'Permanent',
                                    'contract' => 'Contract',
                                    'part_time' => 'Part Time',
                                    'intern' => 'Intern',
                                ])
                                ->required(),

                            Forms\Components\TextInput::make('job_title')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('salary')
                                ->numeric()
                                ->prefix('TSh')
                                ->step(1000),
                        ]),
                ]),

            Forms\Components\Section::make('Contact Details')
                ->schema([
                    Forms\Components\TextInput::make('phone_number')
                        ->tel()
                        ->maxLength(20),

                    Forms\Components\TextInput::make('emergency_contact_name')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('emergency_contact_phone')
                        ->tel()
                        ->maxLength(20),
                ])
                ->columns(3)
                ->collapsed(),
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
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name']),

                Tables\Columns\TextColumn::make('job_title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('appointment_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'probation' => 'warning',
                        'suspended' => 'danger',
                        'terminated' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('contract_type')
                    ->badge(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employment_status')
                    ->options([
                        'active' => 'Active',
                        'probation' => 'Probation',
                        'suspended' => 'Suspended',
                        'terminated' => 'Terminated',
                    ]),

                Tables\Filters\SelectFilter::make('contract_type')
                    ->options([
                        'permanent' => 'Permanent',
                        'contract' => 'Contract',
                        'part_time' => 'Part Time',
                        'intern' => 'Intern',
                    ]),

                Tables\Filters\Filter::make('appointment_date')
                    ->form([
                        Forms\Components\DatePicker::make('appointed_from'),
                        Forms\Components\DatePicker::make('appointed_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['appointed_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('appointment_date', '>=', $date),
                            )
                            ->when(
                                $data['appointed_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('appointment_date', '<=', $date),
                            );
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
