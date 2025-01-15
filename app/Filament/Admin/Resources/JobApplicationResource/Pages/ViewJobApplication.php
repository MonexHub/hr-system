<?php

namespace App\Filament\Admin\Resources\JobApplicationResource\Pages;

use App\Filament\Admin\Resources\JobApplicationResource;
use App\Filament\Admin\Resources\CandidateResource;
use App\Filament\Admin\Resources\JobPostingResource;
use App\Models\JobApplication;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ViewJobApplication extends ViewRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Primary Actions
            Actions\EditAction::make()
                ->modalWidth('lg'),

            // Status Management Actions
            Actions\Action::make('change_status')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Select::make('status')
                        ->options([
                            'under_review' => 'Under Review',
                            'shortlisted' => 'Shortlist',
                            'rejected' => 'Reject',
                        ])
                        ->required()
                        ->live(),

                    Textarea::make('notes')
                        ->required()
                        ->label('Status Change Notes'),

                    Textarea::make('rejection_reason')
                        ->required()
                        ->visible(fn (callable $get) => $get('status') === 'rejected')
                        ->label('Reason for Rejection'),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => $data['status'],
                        'rejection_reason' => $data['rejection_reason'] ?? null,
                        'status_notes' => $data['notes'],
                        'reviewed_at' => now(),
                        'reviewed_by' => auth()->id(),
                    ]);

                    $this->notification()->success('Status updated successfully');
                }),

            // Interview Management
            Actions\Action::make('schedule_interview')
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->form([
                    Select::make('interview_type')
                        ->options([
                            'screening' => 'Initial Screening',
                            'technical' => 'Technical Interview',
                            'hr' => 'HR Interview',
                            'final' => 'Final Interview',
                        ])
                        ->required(),

                    DateTimePicker::make('interview_date')
                        ->required()
                        ->minDate(now())
                        ->timezone(config('app.timezone')),

                    Select::make('interview_mode')
                        ->options([
                            'in_person' => 'In Person',
                            'video' => 'Video Call',
                            'phone' => 'Phone Call',
                        ])
                        ->required()
                        ->live(),

                    TextInput::make('location')
                        ->required()
                        ->label(fn (callable $get) => match ($get('interview_mode')) {
                            'in_person' => 'Interview Location',
                            'video' => 'Meeting Link',
                            'phone' => 'Phone Number',
                            default => 'Location/Link'
                        }),

                    Select::make('interviewer_id')
                        ->relationship('jobPosting.department.users', 'name')
                        ->required()
                        ->searchable(),

                    Textarea::make('notes')
                        ->label('Interview Instructions'),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => 'interview_scheduled',
                        'interview_details' => $data,
                    ]);

                    $this->notification()->success('Interview scheduled successfully');
                })
                ->visible(fn () => in_array($this->record->status, ['submitted', 'under_review', 'shortlisted'])),

            // Document Actions
            Actions\ActionGroup::make([
                Actions\Action::make('download_cv')
                    ->icon('heroicon-o-document-arrow-down')
                    ->label('Download CV')
                    ->url(fn () => $this->record->resume_path ? Storage::url($this->record->resume_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn () => $this->record->resume_path !== null),

                Actions\Action::make('download_cover_letter')
                    ->icon('heroicon-o-document-text')
                    ->label('Download Cover Letter')
                    ->url(fn () => $this->record->cover_letter_path ? Storage::url($this->record->cover_letter_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn () => $this->record->cover_letter_path !== null),

                Actions\Action::make('email')
                    ->icon('heroicon-o-envelope')
                    ->label('Email Candidate')
                    ->url(fn () => 'mailto:' . $this->record->candidate->email),
            ])->label('Documents'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Application Overview
                Infolists\Components\Section::make('Application Overview')
                    ->schema([
                        Infolists\Components\TextEntry::make('application_number')
                            ->label('Reference')
                            ->weight(FontWeight::Bold),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Applied')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => str($state)->title())
                            ->color(fn (string $state): string => match ($state) {
                                'submitted' => 'gray',
                                'under_review' => 'info',
                                'shortlisted' => 'warning',
                                'interview_scheduled', 'interview_completed' => 'warning',
                                'offer_made' => 'success',
                                'offer_accepted', 'hired' => 'success',
                                'rejected', 'withdrawn', 'offer_declined' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('reviewed_at')
                            ->label('Last Review')
                            ->dateTime(),
                    ])
                    ->columns(4),

                // Position and Candidate Information
                Infolists\Components\Grid::make(2)
                    ->schema([
                        Infolists\Components\Section::make('Position Details')
                            ->schema([
                                Infolists\Components\TextEntry::make('jobPosting.title')
                                    ->label('Position')
                                    ->weight(FontWeight::Bold)
                                    ->url(fn ($record) => JobPostingResource::getUrl('view', ['record' => $record->jobPosting]))
                                    ->openUrlInNewTab(),

                                Infolists\Components\TextEntry::make('jobPosting.department.name')
                                    ->label('Department'),

                                Infolists\Components\TextEntry::make('jobPosting.location')
                                    ->label('Location'),

                                Infolists\Components\TextEntry::make('jobPosting.salary_range')
                                    ->label('Salary Range'),
                            ]),

                        Infolists\Components\Section::make('Candidate Information')
                            ->schema([
                                Infolists\Components\ImageEntry::make('candidate.photo_path')
                                    ->label('Photo')
                                    ->circular()
                                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->candidate->full_name)),

                                Infolists\Components\TextEntry::make('candidate.full_name')
                                    ->label('Name')
                                    ->weight(FontWeight::Bold)
                                    ->url(fn ($record) => CandidateResource::getUrl('view', ['record' => $record->candidate]))
                                    ->openUrlInNewTab(),

                                Infolists\Components\TextEntry::make('candidate.email')
                                    ->label('Email')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('candidate.phone')
                                    ->label('Phone')
                                    ->copyable(),
                            ]),
                    ]),

                // Application Details
                Infolists\Components\Section::make('Application Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('cover_letter')
                            ->label('Cover Letter')
                            ->markdown()
                            ->columnSpanFull(),

                        Infolists\Components\RepeatableEntry::make('skills')
                            ->label('Skills & Qualifications')
                            ->schema([
                                Infolists\Components\TextEntry::make('skill')
                                    ->label('Skill'),

                                Infolists\Components\TextEntry::make('years')
                                    ->label('Experience (Years)'),

                                Infolists\Components\TextEntry::make('level')
                                    ->badge(),
                            ])
                            ->columns(3),

                        Infolists\Components\TextEntry::make('experience_summary')
                            ->label('Experience Summary')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // Interview & Assessment
                Infolists\Components\Section::make('Interview & Assessment')
                    ->schema([
                        Infolists\Components\TextEntry::make('interview_feedback')
                            ->label('Interview Feedback')
                            ->markdown()
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->interview_feedback)),

                        Infolists\Components\RepeatableEntry::make('assessment_results')
                            ->schema([
                                Infolists\Components\TextEntry::make('category')
                                    ->label('Category'),

                                Infolists\Components\TextEntry::make('score')
                                    ->label('Score')
                                    ->badge()
                                    ->color(fn (string $state): string => match ((int) $state) {
                                        1, 2 => 'danger',
                                        3 => 'warning',
                                        4 => 'success',
                                        5 => 'success',
                                        default => 'gray',
                                    }),

                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Notes'),
                            ])
                            ->columns(3)
                            ->visible(fn ($record) => !empty($record->assessment_results)),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // Documents Section
                Infolists\Components\Section::make('Documents')
                    ->schema([
                        Infolists\Components\TextEntry::make('resume_path')
                            ->label('Resume/CV')
                            ->hidden(fn ($record) => !$record->resume_path)
                            ->url(fn ($record) => Storage::url($record->resume_path))
                            ->openUrlInNewTab(),

                        Infolists\Components\RepeatableEntry::make('additional_documents')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Document Name'),
                                Infolists\Components\TextEntry::make('path')
                                    ->label('Download')
                                    ->url(fn ($record) => Storage::url($record['path']))
                                    ->openUrlInNewTab(),
                            ]),
                    ])
                    ->collapsible(),

                // Notes & Timeline
                Infolists\Components\Section::make('Application History')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('timeline')
                            ->label('Status Updates')
                            ->schema([
                                Infolists\Components\TextEntry::make('date')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('status')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('notes')
                                    ->markdown(),
                                Infolists\Components\TextEntry::make('user'),
                            ])
                            ->columns(4),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
