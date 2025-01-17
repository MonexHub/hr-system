<?php

namespace App\Filament\Admin\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class EducationsRelationManager extends RelationManager
{
    protected static string $relationship = 'education';

    protected static ?string $title = 'Education History';

    protected static ?string $recordTitleAttribute = 'institution';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('institution')
                ->label('School/Institution')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('degree_level')
                ->options([
                    'high_school' => 'High School',
                    'certificate' => 'Certificate',
                    'diploma' => 'Diploma',
                    'advanced_diploma' => 'Advanced Diploma',
                    'bachelors' => 'Bachelor\'s Degree',
                    'masters' => 'Master\'s Degree',
                    'doctorate' => 'Doctorate',
                    'professional' => 'Professional Certification',
                    'other' => 'Other',
                ])
                ->required(),

            Forms\Components\TextInput::make('field_of_study')
                ->label('Field of Study/Major')
                ->required()
                ->maxLength(255),

            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\DatePicker::make('start_date')
                        ->required()
                        ->maxDate('end_date'),

                    Forms\Components\DatePicker::make('end_date')
                        ->minDate('start_date'),
                ]),

            Forms\Components\TextInput::make('grade')
                ->maxLength(20)
                ->placeholder('e.g., 3.8 GPA, First Class, etc.'),

            Forms\Components\FileUpload::make('certificate')
                ->directory('employee-certificates')
                ->acceptedFileTypes(['application/pdf', 'image/*'])
                ->maxSize(5120)
                ->helperText('Upload certificate (PDF or Image, max 5MB)'),

            Forms\Components\Textarea::make('achievements')
                ->rows(3)
                ->placeholder('Enter any notable achievements, awards, or honors'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('institution')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('degree_level')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('field_of_study')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('period')
                    ->label('Period')
                    ->formatStateUsing(fn ($record) =>
                        $record->start_date->format('M Y') . ' - ' .
                        ($record->end_date ? $record->end_date->format('M Y') : 'Present')
                    )
                    ->sortable('start_date'),

                Tables\Columns\TextColumn::make('grade')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('certificate')
                    ->boolean()
                    ->label('Certificate')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('degree_level')
                    ->options([
                        'high_school' => 'High School',
                        'certificate' => 'Certificate',
                        'diploma' => 'Diploma',
                        'advanced_diploma' => 'Advanced Diploma',
                        'bachelors' => 'Bachelor\'s Degree',
                        'masters' => 'Master\'s Degree',
                        'doctorate' => 'Doctorate',
                        'professional' => 'Professional Certification',
                        'other' => 'Other',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download_certificate')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn ($record) => $record->certificate ? storage::url($record->certificate) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->certificate),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc')
            ->reorderable('sort_order');
    }
}
