<?php

namespace App\Filament\Admin\Resources\JobApplicationResource\Pages;

use App\Filament\Admin\Resources\JobApplicationResource;
use App\Notifications\RecruitmentNotification;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Auth;

class ViewJobApplication extends ViewRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Edit action - Not available for employees
            Actions\EditAction::make()
                ->visible(fn () =>
                    $this->record->status === 'new' &&
                    !Auth::user()->hasRole('employee')),

            // Shortlist action - Only for HR managers and above
            Actions\Action::make('shortlist')
                ->icon('heroicon-o-star')
                ->color('warning')
                ->action(function () {
                    $this->record->shortlist();
                    $this->record->candidate->notify(new RecruitmentNotification('shortlisted', [
                        'job_title' => $this->record->jobPosting->title,
                        'application_id' => $this->record->id
                    ]));
                })
                ->requiresConfirmation()
                ->visible(fn () =>
                    in_array($this->record->status, ['new', 'reviewed']) &&
                    (Auth::user()->hasRole('hr_manager') ||
                        Auth::user()->hasRole('chief_executive_officer') ||
                        Auth::user()->hasRole('super_admin'))),

            // Schedule Interview - Available to department heads and above
            Actions\Action::make('schedule_interview')
                ->icon('heroicon-o-calendar')
                ->color('info')
                ->action(function () {
                    $this->record->scheduleInterview();
                    $this->record->candidate->notify(new RecruitmentNotification('interview_scheduled', [
                        'job_title' => $this->record->jobPosting->title,
                        'interview_id' => $this->record->id,
                        'interview_date' => now()->addDays(7)->toDateString(),
                        'interview_time' => '10:00',
                        'interview_mode' => 'video'
                    ]));
                })
                ->visible(fn () =>
                    $this->record->status === 'shortlisted' &&
                    (Auth::user()->hasRole('department_head') ||
                        Auth::user()->hasRole('hr_manager') ||
                        Auth::user()->hasRole('chief_executive_officer') ||
                        Auth::user()->hasRole('super_admin'))),

            // Hire action - Only for HR managers and above
            Actions\Action::make('hire')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->action(function () {
                    $this->record->hire();
                    $this->record->candidate->notify(new RecruitmentNotification('offer_letter', [
                        'job_title' => $this->record->jobPosting->title,
                        'department' => $this->record->jobPosting->department->name,
                        'start_date' => now()->addDays(30)->format('Y-m-d'),
                        'valid_until' => now()->addDays(7)->format('Y-m-d')
                    ]));
                })
                ->requiresConfirmation()
                ->visible(fn () =>
                    $this->record->status === 'interview_completed' &&
                    (Auth::user()->hasRole('hr_manager') ||
                        Auth::user()->hasRole('chief_executive_officer') ||
                        Auth::user()->hasRole('super_admin'))),

            // Reject action - Only for HR managers and above
            Actions\Action::make('reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(function () {
                    $this->record->reject();
                    $this->record->candidate->notify(new RecruitmentNotification('rejected', [
                        'job_title' => $this->record->jobPosting->title
                    ]));
                })
                ->requiresConfirmation()
                ->visible(fn () =>
                    !in_array($this->record->status, ['rejected', 'hired']) &&
                    (Auth::user()->hasRole('hr_manager') ||
                        Auth::user()->hasRole('chief_executive_officer') ||
                        Auth::user()->hasRole('super_admin'))),

            // Download Resume - Available to all roles
            Actions\Action::make('download_resume')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => storage_path('app/public/' . $this->record->resume_path))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Application Status
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
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
                    ])
                    ->columnSpanFull(),

                // Job Details
                Infolists\Components\Section::make('Position Applied For')
                    ->schema([
                        Infolists\Components\TextEntry::make('jobPosting.title')
                            ->label('Position')
                            ->weight(FontWeight::Bold),

                        Infolists\Components\TextEntry::make('jobPosting.department.name')
                            ->label('Department'),

                        Infolists\Components\TextEntry::make('jobPosting.position_code')
                            ->label('Reference Code'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Application Date')
                            ->dateTime(),
                    ])
                    ->columns(4),

                // Personal Information
                Infolists\Components\Section::make('Personal Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('first_name')
                            ->label('First Name'),

                        Infolists\Components\TextEntry::make('last_name')
                            ->label('Last Name'),

                        Infolists\Components\TextEntry::make('email')
                            ->icon('heroicon-m-envelope'),

                        Infolists\Components\TextEntry::make('phone')
                            ->icon('heroicon-m-phone'),
                    ])
                    ->columns(2),

                // Professional Information
                Infolists\Components\Section::make('Professional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('current_position')
                            ->label('Current Position'),

                        Infolists\Components\TextEntry::make('current_company')
                            ->label('Current Company'),

                        Infolists\Components\TextEntry::make('experience_years')
                            ->label('Years of Experience'),

                        Infolists\Components\TextEntry::make('education_level')
                            ->label('Education Level'),

                        Infolists\Components\TextEntry::make('expected_salary')
                            ->label('Expected Salary')
                            ->money('USD'),

                        Infolists\Components\TextEntry::make('notice_period')
                            ->label('Notice Period'),
                    ])
                    ->columns(3),

                // Documents
                Infolists\Components\Section::make('Documents & Links')
                    ->schema([
                        Infolists\Components\TextEntry::make('resume_path')
                            ->label('Resume/CV')
                            ->url(fn ($record) => storage_path('app/public/' . $record->resume_path))
                            ->openUrlInNewTab()
                            ->icon('heroicon-m-document'),

                        Infolists\Components\TextEntry::make('cover_letter_path')
                            ->label('Cover Letter')
                            ->url(fn ($record) => $record->cover_letter_path ?
                                storage_path('app/public/' . $record->cover_letter_path) : null)
                            ->openUrlInNewTab()
                            ->visible(fn ($record) => $record->cover_letter_path)
                            ->icon('heroicon-m-document-text'),

                        Infolists\Components\TextEntry::make('portfolio_url')
                            ->label('Portfolio')
                            ->url(fn ($record) => $record->portfolio_url)
                            ->openUrlInNewTab()
                            ->visible(fn ($record) => $record->portfolio_url)
                            ->icon('heroicon-m-globe-alt'),

                        Infolists\Components\TextEntry::make('linkedin_url')
                            ->label('LinkedIn')
                            ->url(fn ($record) => $record->linkedin_url)
                            ->openUrlInNewTab()
                            ->visible(fn ($record) => $record->linkedin_url)
                            ->icon('heroicon-s-square-3-stack-3d'),
                    ])
                    ->columns(2),

                // Additional Information
                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('referral_source')
                            ->label('Referral Source'),

                        Infolists\Components\TextEntry::make('additional_notes')
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                // Review Information - Hidden for employees
                Infolists\Components\Section::make('Review Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('reviewer.name')
                            ->label('Reviewed By')
                            ->visible(fn ($record) => $record->reviewed_by),

                        Infolists\Components\TextEntry::make('reviewed_at')
                            ->label('Review Date')
                            ->dateTime()
                            ->visible(fn ($record) => $record->reviewed_at),
                    ])
                    ->columns(2)
                    ->visible(fn () =>
                        (Auth::user()->hasRole('department_head') ||
                            Auth::user()->hasRole('hr_manager') ||
                            Auth::user()->hasRole('chief_executive_officer') ||
                            Auth::user()->hasRole('super_admin')) &&
                        ($this->record->reviewed_by || $this->record->reviewed_at)),
            ]);
    }
}
