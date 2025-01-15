<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JobPostingResource\Pages;
use App\Models\JobPosting;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPostingResource extends Resource
{
    protected static ?string $model = JobPosting::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('department_id')
                            ->relationship('department', 'name')
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('position_code')
                            ->required()
                            ->unique(ignoreRecord: true)
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
                    ])->columns(2),

                Forms\Components\Section::make('Job Details')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TagsInput::make('requirements')
                            ->separator(',')
                            ->columnSpanFull()
                            ->helperText('Separate multiple requirements with commas')
                            ->saveRelationshipsUsing(function ($record, $state) {
                                // Ensure $state is an array before saving
                                $record->requirements = is_array($state) ? $state : explode(',', $state);
                            })
                            ->columnSpanFull(),

                        Forms\Components\TagsInput::make('responsibilities')
                            ->separator(',')
                            ->columnSpanFull()
                            ->saveRelationshipsUsing(function ($record, $state) {
                                $record->responsibilities = is_array($state) ? $state : explode(',', $state);
                            })
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Location & Work Type')
                    ->schema([
                        Forms\Components\TextInput::make('location')
                            ->required(),

                        Forms\Components\Toggle::make('is_remote')
                            ->label('Remote Position'),
                    ])->columns(2),

                Forms\Components\Section::make('Salary Information')
                    ->schema([
                        Forms\Components\TextInput::make('salary_min')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\TextInput::make('salary_max')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\Select::make('salary_currency')
                            ->options([
                                'TZS' => 'TZS',
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                            ])
                            ->default('USD'),

                        Forms\Components\Toggle::make('hide_salary')
                            ->label('Hide Salary Range'),
                    ])->columns(2),

                Forms\Components\Section::make('Posting Details')
                    ->schema([
                        Forms\Components\DatePicker::make('publishing_date'),
                        Forms\Components\DatePicker::make('closing_date'),
                        Forms\Components\TextInput::make('positions_available')
                            ->numeric()
                            ->default(1),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Feature this posting'),
                    ])->columns(2),

                Forms\Components\Section::make('Requirements & Benefits')
                    ->schema([
                        Forms\Components\TagsInput::make('skills_required')
                            ->separator(',')
                            ->saveRelationshipsUsing(function ($record, $state) {
                                $record->skills_required = is_array($state) ? $state : explode(',', $state);
                            }),

                        Forms\Components\TagsInput::make('education_requirements')
                            ->separator(',')
                            ->saveRelationshipsUsing(function ($record, $state) {
                                $record->education_requirements = is_array($state) ? $state : explode(',', $state);
                            }),

                        Forms\Components\TagsInput::make('experience_requirements')
                            ->separator(',')
                            ->saveRelationshipsUsing(function ($record, $state) {
                                $record->experience_requirements = is_array($state) ? $state : explode(',', $state);
                            }),
                        Forms\Components\TagsInput::make('benefits')
                            ->separator(',')
                            ->saveRelationshipsUsing(function ($record, $state) {
                                $record->benefits = is_array($state) ? $state : explode(',', $state);
                            }),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('position_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending_approval' => 'warning',
                        'published' => 'success',
                        'closed' => 'danger',
                        'cancelled' => 'danger',
                        'filled' => 'info',
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
                        'pending_approval' => 'Pending Approval',
                        'published' => 'Published',
                        'closed' => 'Closed',
                        'cancelled' => 'Cancelled',
                        'filled' => 'Filled',
                    ]),
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobPostings::route('/'),
            'create' => Pages\CreateJobPosting::route('/create'),
            'edit' => Pages\EditJobPosting::route('/{record}/edit'),
            'view' => Pages\ViewJobPosting::route('/{record}'),
        ];
    }
}
