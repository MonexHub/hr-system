<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JobApplicationResource\Pages;
use App\Models\JobApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Colors\Color;

class JobApplicationResource extends Resource
{
    protected static ?string $model = JobApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Job Selection')
                    ->schema([
                        Forms\Components\Select::make('job_posting_id')
                            ->relationship('jobPosting', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                    ]),

                Forms\Components\Section::make('Personal Information')
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
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Professional Information')
                    ->schema([
                        Forms\Components\TextInput::make('current_position')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('current_company')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('experience_years')
                            ->numeric()
                            ->required()
                            ->step(0.5)
                            ->minValue(0)
                            ->maxValue(50),

                        Forms\Components\Select::make('education_level')
                            ->options([
                                'high_school' => 'High School',
                                'associate' => 'Associate Degree',
                                'bachelor' => 'Bachelor\'s Degree',
                                'master' => 'Master\'s Degree',
                                'phd' => 'PhD/Doctorate',
                                'other' => 'Other',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Documents')
                    ->schema([
                        Forms\Components\FileUpload::make('resume_path')
                            ->label('Resume/CV')
                            ->required()
                            ->directory('applications/resumes')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120),

                        Forms\Components\FileUpload::make('cover_letter_path')
                            ->label('Cover Letter')
                            ->directory('applications/cover-letters')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120),

                        Forms\Components\FileUpload::make('other_attachments')
                            ->multiple()
                            ->directory('applications/attachments')
                            ->maxFiles(3)
                            ->maxSize(5120),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\TextInput::make('portfolio_url')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('linkedin_url')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('expected_salary')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\TextInput::make('notice_period')
                            ->maxLength(50),

                        Forms\Components\Select::make('referral_source')
                            ->options([
                                'company_website' => 'Company Website',
                                'linkedin' => 'LinkedIn',
                                'job_board' => 'Job Board',
                                'employee_referral' => 'Employee Referral',
                                'other' => 'Other',
                            ]),

                        Forms\Components\Textarea::make('additional_notes')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jobPosting.title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (JobApplication $record): string =>
                    $record->jobPosting->position_code),

                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('experience_years')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'gray',
                        'reviewed' => 'info',
                        'shortlisted' => 'warning',
                        'interview_scheduled' => 'purple',
                        'interview_completed' => 'blue',
                        'hired' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'reviewed' => 'Reviewed',
                        'shortlisted' => 'Shortlisted',
                        'interview_scheduled' => 'Interview Scheduled',
                        'interview_completed' => 'Interview Completed',
                        'hired' => 'Hired',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('job_posting')
                    ->relationship('jobPosting', 'title'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // Download Resume
                Tables\Actions\Action::make('download_resume')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (JobApplication $record) => storage_path('app/public/' . $record->resume_path))
                    ->openUrlInNewTab(),

                // Shortlist
                Tables\Actions\Action::make('shortlist')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(fn (JobApplication $record) => $record->shortlist())
                    ->visible(fn (JobApplication $record): bool =>
                    in_array($record->status, ['new', 'reviewed'])),

                // Schedule Interview
                Tables\Actions\Action::make('schedule_interview')
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->action(fn (JobApplication $record) => $record->scheduleInterview())
                    ->visible(fn (JobApplication $record): bool =>
                        $record->status === 'shortlisted'),

                // Complete Interview
                Tables\Actions\Action::make('complete_interview')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (JobApplication $record) => $record->completeInterview())
                    ->visible(fn (JobApplication $record): bool =>
                        $record->status === 'interview_scheduled'),

                // Hire
                Tables\Actions\Action::make('hire')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->action(fn (JobApplication $record) => $record->hire())
                    ->visible(fn (JobApplication $record): bool =>
                        $record->status === 'interview_completed'),

                // Reject
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (JobApplication $record) => $record->reject())
                    ->visible(fn (JobApplication $record): bool =>
                    !in_array($record->status, ['rejected', 'hired'])),
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
            'index' => Pages\ListJobApplications::route('/'),
            'create' => Pages\CreateJobApplication::route('/create'),
            'view' => Pages\ViewJobApplication::route('/{record}'),
            'edit' => Pages\EditJobApplication::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
