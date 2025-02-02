<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JobPostingResource\Pages;
use App\Models\JobPosting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Colors\Color;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class JobPostingResource extends Resource
{
    protected static ?string $model = JobPosting::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Recruitment';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Job Posting')
                ->tabs([
                    // Document Upload Tab
                    Forms\Components\Tabs\Tab::make('Upload Job Description')
                        ->schema([
                            Forms\Components\Toggle::make('is_document_based')
                                ->label('Use Document Upload')
                                ->default(false)
                                ->reactive(),

                            Forms\Components\Section::make('Quick Upload')
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\Select::make('department_id')
                                        ->relationship('department', 'name')
                                        ->required()
                                        ->searchable(),

                                    Forms\Components\Select::make('employment_type')
                                        ->options([
                                            'full_time' => 'Full Time',
                                            'part_time' => 'Part Time',
                                            'contract' => 'Contract',
                                            'temporary' => 'Temporary',
                                            'internship' => 'Internship',
                                        ])
                                        ->required(),

                                    Forms\Components\FileUpload::make('document_path')
                                        ->label('Job Description Document')
                                        ->directory('job-descriptions')
                                        ->acceptedFileTypes([
                                            'application/pdf',
                                            'application/msword',
                                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                                        ])
                                        ->maxSize(5120)
                                        ->required()
                                        ->downloadable()
                                        ->helperText('Upload PDF or Word document (max 5MB)'),

                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\DatePicker::make('publishing_date')
                                            ->required(),

                                        Forms\Components\DatePicker::make('closing_date')
                                            ->required(),
                                    ]),

                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('positions_available')
                                            ->numeric()
                                            ->default(1),

                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Feature this posting'),
                                    ]),
                                ])
                                ->columns(2)
                                ->visible(fn (callable $get) => $get('is_document_based')),
                        ]),

                    // Detailed Form Tab
                    Forms\Components\Tabs\Tab::make('Detailed Form')
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
                                        ->columnSpanFull(),

                                    Forms\Components\TagsInput::make('responsibilities')
                                        ->separator(',')
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
                                        ->prefix(fn (callable $get) => $get('salary_currency')),

                                    Forms\Components\TextInput::make('salary_max')
                                        ->numeric()
                                        ->prefix(fn (callable $get) => $get('salary_currency')),

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
                                    Forms\Components\DatePicker::make('publishing_date')
                                        ->required(),
                                    Forms\Components\DatePicker::make('closing_date')
                                        ->required(),
                                    Forms\Components\TextInput::make('positions_available')
                                        ->numeric()
                                        ->default(1),
                                    Forms\Components\Toggle::make('is_featured')
                                        ->label('Feature this posting'),
                                ])->columns(2),

                            Forms\Components\Section::make('Requirements & Benefits')
                                ->schema([
                                    Forms\Components\TagsInput::make('skills_required')
                                        ->separator(','),
                                    Forms\Components\TagsInput::make('education_requirements')
                                        ->separator(','),
                                    Forms\Components\TagsInput::make('experience_requirements')
                                        ->separator(','),
                                    Forms\Components\TagsInput::make('benefits')
                                        ->separator(','),
                                ])->columns(2),
                        ])
                        ->visible(fn (callable $get) => !$get('is_document_based')),
                ])->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('position_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('department.name')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_document_based')
                    ->boolean()
                    ->label('Doc Based'),
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
                Tables\Columns\TextColumn::make('positions_available')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('publishing_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('closing_date')
                    ->date()
                    ->sortable()
                    ->color(fn (JobPosting $record): string =>
                    $record->closing_date?->isPast() ? 'danger' : 'success'),
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
                Tables\Filters\SelectFilter::make('employment_type')
                    ->options([
                        'full_time' => 'Full Time',
                        'part_time' => 'Part Time',
                        'contract' => 'Contract',
                        'temporary' => 'Temporary',
                        'internship' => 'Internship',
                    ]),
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->active()),
                Tables\Filters\Filter::make('featured')
                    ->query(fn (Builder $query): Builder => $query->featured()),
            ])
            ->actions([
                // View action
                Tables\Actions\ViewAction::make(),

                // Edit action
                Tables\Actions\EditAction::make()
                    ->visible(fn (JobPosting $record): bool =>
                        in_array($record->status, ['draft', 'pending_approval']) &&
                        auth()->user()->hasRole('hr_manager')),

                // Submit for Approval
                Tables\Actions\Action::make('submit_approval')
                    ->icon('heroicon-o-paper-airplane')
                    ->color(Color::Amber)
                    ->requiresConfirmation()
                    ->modalHeading('Submit for Approval')
                    ->modalDescription('Are you sure you want to submit this job posting for approval?')
                    ->modalSubmitActionLabel('Yes, submit')
                    ->action(fn (JobPosting $record) => $record->update(['status' => 'pending_approval']))
                    ->visible(fn (JobPosting $record): bool =>
                        $record->status === 'draft' &&
                        auth()->user()->hasRole('hr_manager')),

                // Approve and Publish
                Tables\Actions\Action::make('approve_publish')
                    ->icon('heroicon-o-check-circle')
                    ->color(Color::Green)
                    ->requiresConfirmation()
                    ->modalHeading('Approve and Publish')
                    ->modalDescription('This will publish the job posting. Are you sure?')
                    ->modalSubmitActionLabel('Yes, publish')
                    ->action(fn (JobPosting $record) => $record->approve(auth()->id()))
                    ->visible(fn (JobPosting $record): bool =>
                        $record->status === 'pending_approval' &&
                        auth()->user()->hasRole('hr_manager')),

                // Reject Back to Draft
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color(Color::Red)
                    ->requiresConfirmation()
                    ->modalHeading('Reject Job Posting')
                    ->modalDescription('This will return the posting to draft status. Are you sure?')
                    ->modalSubmitActionLabel('Yes, reject')
                    ->action(fn (JobPosting $record) => $record->reject())
                    ->visible(fn (JobPosting $record): bool =>
                        $record->status === 'pending_approval' &&
                        auth()->user()->hasRole('hr_manager')),

                // Close Posting
                Tables\Actions\Action::make('close')
                    ->icon('heroicon-o-lock-closed')
                    ->color(Color::Red)
                    ->requiresConfirmation()
                    ->modalHeading('Close Job Posting')
                    ->modalDescription('This will close the job posting. Are you sure?')
                    ->modalSubmitActionLabel('Yes, close')
                    ->action(fn (JobPosting $record) => $record->close())
                    ->visible(fn (JobPosting $record): bool =>
                        $record->status === 'published' &&
                        auth()->user()->hasRole('hr_manager')),

                // Mark as Filled
                Tables\Actions\Action::make('mark_filled')
                    ->icon('heroicon-o-user-group')
                    ->color(Color::Green)
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Filled')
                    ->modalDescription('This will mark the position as filled and close the posting. Continue?')
                    ->modalSubmitActionLabel('Yes, mark as filled')
                    ->action(fn (JobPosting $record) => $record->markAsFilled())
                    ->visible(fn (JobPosting $record): bool =>
                        $record->status === 'published' &&
                        auth()->user()->hasRole('hr_manager')),

                // Download Document
                Tables\Actions\Action::make('download_document')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (JobPosting $record) =>
                    $record->is_document_based ? Storage::url($record->document_path) : null,
                        true)
                    ->openUrlInNewTab()
                    ->visible(fn (JobPosting $record): bool =>
                        $record->is_document_based && $record->document_path),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()->hasRole('hr_manager')),

                    // Bulk Close
                    Tables\Actions\BulkAction::make('bulk_close')
                        ->label('Close Selected')
                        ->icon('heroicon-o-lock-closed')
                        ->color(Color::Red)
                        ->requiresConfirmation()
                        ->modalHeading('Close Selected Postings')
                        ->modalDescription('Are you sure you want to close all selected job postings?')
                        ->modalSubmitActionLabel('Yes, close all')
                        ->action(fn (Collection $records) => $records->each->close())
                        ->deselectRecordsAfterCompletion()
                        ->visible(fn (): bool => auth()->user()->hasRole('hr_manager')),

                    // Bulk Approve
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color(Color::Green)
                        ->requiresConfirmation()
                        ->modalHeading('Approve Selected Postings')
                        ->modalDescription('Are you sure you want to approve all selected job postings?')
                        ->modalSubmitActionLabel('Yes, approve all')
                        ->action(fn (Collection $records) => $records->each(fn ($record) =>
                        $record->approve(auth()->id())))
                        ->deselectRecordsAfterCompletion()
                        ->visible(fn (): bool => auth()->user()->hasRole('hr_manager')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Add relations if needed
        ];
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending_approval')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}

