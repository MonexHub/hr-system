<?php

namespace App\Filament\Employee\Resources\ProfileResource\RelationManagers;

use App\Models\EmployeeSkill;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Colors\Color;

class SkillsRelationManager extends RelationManager
{
    protected static string $relationship = 'skills';
    protected static ?string $title = 'Skills & Competencies';
    protected static ?string $recordTitleAttribute = 'skill_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Skill Information')
                    ->description('Add or edit skill details')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('skill_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Skill Name')
                                    ->placeholder('Enter skill name'),

                                Forms\Components\Select::make('category')
                                    ->options(collect(EmployeeSkill::getCategories())->mapWithKeys(fn ($category) => [
                                        $category => str($category)->title()->replace('_', ' ')
                                    ]))
                                    ->required()
                                    ->native(false)
                                    ->searchable(),

                                Forms\Components\Select::make('proficiency_level')
                                    ->options(collect(EmployeeSkill::getProficiencyLevels())->mapWithKeys(fn ($level) => [
                                        $level => str($level)->title()
                                    ]))
                                    ->required()
                                    ->native(false)
                                    ->helperText('Select your proficiency level in this skill'),

                                Forms\Components\TextInput::make('years_of_experience')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(50)
                                    ->suffix('years')
                                    ->placeholder('Years of experience')
                                    ->helperText('Enter the number of years of experience'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->placeholder('Describe your experience and achievements with this skill')
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('skill_name')
                    ->searchable()
                    ->sortable()
                    ->label('Skill'),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'technical' => 'info',
                        'soft_skills' => 'success',
                        'languages' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => str($state)->title()->replace('_', ' '))
                    ->sortable(),

                Tables\Columns\TextColumn::make('proficiency_level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'beginner' => 'gray',
                        'intermediate' => 'info',
                        'advanced' => 'warning',
                        'expert' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => str($state)->title())
                    ->sortable(),

                Tables\Columns\TextColumn::make('years_of_experience')
                    ->numeric()
                    ->suffix(' years')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('skill_name')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(collect(EmployeeSkill::getCategories())->mapWithKeys(fn ($category) => [
                        $category => str($category)->title()->replace('_', ' ')
                    ])),

                Tables\Filters\SelectFilter::make('proficiency_level')
                    ->options(collect(EmployeeSkill::getProficiencyLevels())->mapWithKeys(fn ($level) => [
                        $level => str($level)->title()
                    ])),
            ])
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
