<?php

namespace App\Filament\Admin\Resources\JobApplicationResource\Pages;

use App\Filament\Admin\Resources\JobApplicationResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewJobApplication extends ViewRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->status === 'new'),

            // Shortlist Action
            Actions\Action::make('shortlist')
                ->icon('heroicon-o-star')
                ->color('warning')
                ->action(fn () => $this->record->shortlist())
                ->requiresConfirmation()
                ->modalHeading('Shortlist Candidate')
                ->modalDescription('Are you sure you want to shortlist this candidate?')
                ->visible(fn () => in_array($this->record->status, ['new', 'reviewed'])),

            // Schedule Interview
            Actions\Action::make('schedule_interview')
                ->icon('heroicon-o-calendar')
                ->color('info')
                ->action(fn () => $this->record->scheduleInterview())
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'shortlisted'),

            // Complete Interview
            Actions\Action::make('complete_interview')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(fn () => $this->record->completeInterview())
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'interview_scheduled'),

            // Hire
            Actions\Action::make('hire')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->action(fn () => $this->record->hire())
                ->requiresConfirmation()
                ->modalHeading('Hire Candidate')
                ->modalDescription('This will mark the candidate as hired. Continue?')
                ->visible(fn () => $this->record->status === 'interview_completed'),

            // Reject
            Actions\Action::make('reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject Application')
                ->modalDescription('Are you sure you want to reject this application?')
                ->action(fn () => $this->record->reject())
                ->visible(fn () => !in_array($this->record->status, ['rejected', 'hired'])),

            // Download Resume
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
                            ->url()
                            ->openUrlInNewTab()
                            ->visible(fn ($record) => $record->portfolio_url)
                            ->icon('heroicon-m-globe-alt'),

                        Infolists\Components\TextEntry::make('linkedin_url')
                            ->label('LinkedIn')
                            ->url()
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

                // Review Information
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
                    ->visible(fn ($record) => $record->reviewed_by || $record->reviewed_at),
            ]);
    }
}
