<?php

namespace App\Filament\Admin\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExperienceRelationManager extends RelationManager
{
    protected static string $relationship = 'workExperiences';

    protected static ?string $title = 'Work Experience';

    protected static ?string $recordTitleAttribute = 'job_title';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('organization')
                ->label('Company/Organization')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('job_title')
                ->label('Position/Title')
                ->required()
                ->maxLength(255),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\DatePicker::make('start_date')
                        ->required()
                        ->maxDate('end_date')
                        ->displayFormat('M Y'),

                    Forms\Components\DatePicker::make('end_date')
                        ->minDate('start_date')
                        ->displayFormat('M Y')
                        ->hidden(fn (callable $get) => $get('is_current')),

                    Forms\Components\Toggle::make('is_current')
                        ->label('Current Position')
                        ->reactive(),
                ]),

            Forms\Components\TextInput::make('location')
                ->maxLength(255),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('supervisor_name')
                        ->label('Supervisor\'s Name')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('supervisor_contact')
                        ->label('Supervisor\'s Contact')
                        ->tel()
                        ->maxLength(255),
                ]),

            Forms\Components\RichEditor::make('responsibilities')
                ->toolbarButtons([
                    'bold',
                    'bulletList',
                    'orderedList',
                ])
                ->required(),

            Forms\Components\Textarea::make('achievements')
                ->rows(3)
                ->placeholder('Enter key achievements and accomplishments'),

            Forms\Components\TextInput::make('reason_for_leaving')
                ->maxLength(255)
                ->hidden(fn (callable $get) => $get('is_current')),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('organization')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('job_title')
                    ->label('Position')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('period')
                    ->formatStateUsing(fn ($record) =>
                        $record->start_date->format('M Y') . ' - ' .
                        ($record->is_current ? 'Present' : $record->end_date->format('M Y'))
                    )
                    ->description(fn ($record) => $record->location)
                    ->sortable('start_date'),

                Tables\Columns\IconColumn::make('is_current')
                    ->boolean()
                    ->label('Current')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('supervisor_name')
                    ->label('Supervisor')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_current')
                    ->label('Current Position'),
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
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }
}
