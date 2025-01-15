<?php

namespace App\Filament\Admin\Resources\DepartmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobPostingsRelationManager extends RelationManager
{
    protected static string $relationship = 'jobPostings';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Job Postings';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Job Details')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('employment_type')
                        ->options([
                            'full_time' => 'Full Time',
                            'part_time' => 'Part Time',
                            'contract' => 'Contract',
                            'temporary' => 'Temporary',
                            'internship' => 'Internship',
                        ])
                        ->required(),

                    Forms\Components\RichEditor::make('description')
                        ->required()
                        ->toolbarButtons([
                            'bold',
                            'bulletList',
                            'orderedList',
                        ]),

                    Forms\Components\TextInput::make('location')
                        ->required(),

                    Forms\Components\Toggle::make('is_remote')
                        ->label('Remote Work Available'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Salary Information')
                ->schema([
                    Forms\Components\TextInput::make('salary_min')
                        ->numeric()
                        ->prefix('TSh')
                        ->step(1000),

                    Forms\Components\TextInput::make('salary_max')
                        ->numeric()
                        ->prefix('TSh')
                        ->step(1000),

                    Forms\Components\Toggle::make('hide_salary')
                        ->label('Hide Salary Range'),
                ])
                ->columns(3),

            Forms\Components\Section::make('Posting Details')
                ->schema([
                    Forms\Components\DatePicker::make('publishing_date')
                        ->default(now()),

                    Forms\Components\DatePicker::make('closing_date')
                        ->afterOrEqual('publishing_date'),

                    Forms\Components\TextInput::make('positions_available')
                        ->numeric()
                        ->default(1)
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'closed' => 'Closed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('draft')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('position_code')
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employment_type')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_remote')
                    ->boolean(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'closed' => 'danger',
                        'cancelled' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('applications_count')
                    ->counts('applications')
                    ->label('Applications'),

                Tables\Columns\TextColumn::make('publishing_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('closing_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'closed' => 'Closed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\TernaryFilter::make('is_remote')
                    ->label('Remote Work'),

                Tables\Filters\Filter::make('active_dates')
                    ->form([
                        Forms\Components\DatePicker::make('published_from'),
                        Forms\Components\DatePicker::make('published_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['published_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('publishing_date', '>=', $date),
                            )
                            ->when(
                                $data['published_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('publishing_date', '<=', $date),
                            );
                    }),
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
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
