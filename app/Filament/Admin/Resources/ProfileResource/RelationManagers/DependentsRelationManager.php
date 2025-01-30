<?php

namespace App\Filament\Admin\Resources\ProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DependentsRelationManager extends RelationManager
{
    protected static string $relationship = 'dependents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('relationship')
                            ->options([
                                'spouse' => 'Spouse',
                                'child' => 'Child',
                                'parent' => 'Parent',
                                'sibling' => 'Sibling',
                                'other' => 'Other',
                            ])
                            ->required(),

                        Forms\Components\DatePicker::make('date_of_birth')
                            ->displayFormat('d M Y')
                            ->maxDate(now()),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\Select::make('county_id')
                            ->relationship('county', 'name')
                            ->searchable()
                            ->preload(),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('relationship')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'spouse' => 'primary',
                        'child' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('phone'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['employee_id'] = auth()->user()->employee->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
