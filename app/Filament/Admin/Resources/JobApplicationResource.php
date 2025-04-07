<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JobApplicationResource\Pages;
use App\Models\JobApplication;
use App\Notifications\RecruitmentNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

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
                Forms\Components\Section::make('Application Information')
                    ->schema([
                        Forms\Components\TextInput::make('application_number')
                            ->required()
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (string $context): bool => $context === 'edit'),

                        Forms\Components\Select::make('job_posting_id')
                            ->relationship('jobPosting', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (string $context): bool => $context === 'edit'),

                        Forms\Components\Select::make('candidate_id')
                            ->relationship('candidate', 'email')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'submitted' => 'Submitted',
                                'under_review' => 'Under Review',
                                'shortlisted' => 'Shortlisted',
                                'interview_scheduled' => 'Interview Scheduled',
                                'hired' => 'Hired',
                                'rejected' => 'Rejected'
                            ])
                            ->required()
                            ->default('submitted'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Documents')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_letter_path')
                            ->label('Cover Letter')
                            ->directory('applications/cover-letters')
                            ->preserveFilenames()
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(5120),

                        Forms\Components\FileUpload::make('additional_documents')
                            ->multiple()
                            ->preserveFilenames()
                            ->directory('applications/attachments')
                            ->maxFiles(5)
                            ->maxSize(5120),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Review Information')
                    ->schema([
                        Forms\Components\Select::make('reviewed_by')
                            ->relationship('reviewer', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('reviewed_at'),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('interview_feedback')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('assessment_results')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('application_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jobPosting.title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (JobApplication $record): string =>
                        $record->jobPosting->position_code ?? ''),

                Tables\Columns\TextColumn::make('candidate.email')
                    ->searchable()
                    ->sortable()
                    ->label('Candidate Email')
                    ->formatStateUsing(fn (JobApplication $record) =>
                        $record->candidate?->email ?? 'No Email'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'gray',
                        'under_review' => 'info',
                        'shortlisted' => 'warning',
                        'interview_scheduled' => 'purple',
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
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'shortlisted' => 'Shortlisted',
                        'interview_scheduled' => 'Interview Scheduled',
                        'hired' => 'Hired',
                        'rejected' => 'Rejected'
                    ]),

                Tables\Filters\SelectFilter::make('job_posting')
                    ->relationship('jobPosting', 'title'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // Edit action - Restricted for employees
                Tables\Actions\EditAction::make()
                    ->visible(fn () => !Auth::user()->hasRole('employee')),

                // Cover Letter download - Available to all
                Tables\Actions\Action::make('download_cover_letter')
                    ->icon('heroicon-o-document-arrow-down')
                    ->label('Cover Letter')
                    ->url(fn ($record) => $record->cover_letter_path ? Storage::disk('public')->url($record->cover_letter_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->cover_letter_path !== null),

                // Shortlist action - Only for HR managers and above (not for employees or department heads)
                Tables\Actions\Action::make('shortlist')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(function (JobApplication $record) {
                        $record->shortlist();
                        $record->candidate->notify(new RecruitmentNotification('shortlisted', [
                            'job_title' => $record->jobPosting->title,
                            'application_id' => $record->id
                        ]));
                    })
                    ->visible(fn ($record) =>
                        in_array($record->status, ['submitted', 'under_review']) &&
                        (Auth::user()->hasRole('hr_manager') ||
                            Auth::user()->hasRole('chief_executive_officer') ||
                            Auth::user()->hasRole('super_admin'))),

                // Schedule Interview - Available to department heads and above
                Tables\Actions\Action::make('schedule_interview')
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->action(function (JobApplication $record) {
                        $record->scheduleInterview();
                        $record->candidate->notify(new RecruitmentNotification('interview_scheduled', [
                            'job_title' => $record->jobPosting->title,
                            'application_id' => $record->id,
                            'interview_date' => now()->addDays(7)->toDateString()
                        ]));
                    })
                    ->visible(fn ($record) =>
                        $record->status === 'shortlisted' &&
                        (Auth::user()->hasRole('department_head') ||
                            Auth::user()->hasRole('hr_manager') ||
                            Auth::user()->hasRole('chief_executive_officer') ||
                            Auth::user()->hasRole('super_admin'))),

                // Hire action - Only for HR managers and above
                Tables\Actions\Action::make('hire')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->action(function (JobApplication $record) {
                        $record->hire();
                        $record->candidate->notify(new RecruitmentNotification('hired', [
                            'job_title' => $record->jobPosting->title,
                            'application_id' => $record->id
                        ]));
                    })
                    ->visible(fn ($record) =>
                        $record->status === 'interview_scheduled' &&
                        (Auth::user()->hasRole('hr_manager') ||
                            Auth::user()->hasRole('chief_executive_officer') ||
                            Auth::user()->hasRole('super_admin'))),

                // Reject action - Only for HR managers and above
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (JobApplication $record) {
                        $record->reject();
                        $record->candidate->notify(new RecruitmentNotification('rejected', [
                            'job_title' => $record->jobPosting->title,
                            'application_id' => $record->id
                        ]));
                    })
                    ->visible(fn ($record) =>
                        !in_array($record->status, ['rejected', 'hired']) &&
                        (Auth::user()->hasRole('hr_manager') ||
                            Auth::user()->hasRole('chief_executive_officer') ||
                            Auth::user()->hasRole('super_admin'))),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => !Auth::user()->hasRole('employee') && !Auth::user()->hasRole('department_head')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
        return static::getModel()::where('status', 'submitted')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        // Only HR managers and above can create new job applications
        return Auth::user()->hasRole('hr_manager') ||
            Auth::user()->hasRole('chief_executive_officer') ||
            Auth::user()->hasRole('super_admin');
    }
}
