<?php

namespace App\Filament\Admin\Resources\InterviewScheduleResource\Pages;

use App\Filament\Admin\Resources\InterviewScheduleResource;
use App\Filament\Admin\Resources\JobApplicationResource;
use App\Notifications\RecruitmentNotification;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\DB;

class ViewInterviewSchedule extends ViewRecord
{
    protected static string $resource = InterviewScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('mark_completed')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->form([
                    Textarea::make('feedback')->required(),
                    TextInput::make('rating')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(5)
                        ->required(),
                    Textarea::make('recommendations'),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $this->record->update([
                            'status' => 'completed',
                            'feedback' => $data['feedback'],
                            'rating' => $data['rating'],
                            'recommendations' => $data['recommendations'],
                        ]);

                        if ($this->record->jobApplication) {
                            $this->record->jobApplication->update(['status' => 'interview_completed']);

                            if ($candidate = $this->record->jobApplication->candidate) {
                                $candidate->update(['status' => 'interview']);
                                $candidate->notify(new RecruitmentNotification('interview_completed', [
                                    'job_title' => $this->record->jobApplication->jobPosting->title,
                                    'feedback' => $data['feedback'],
                                    'rating' => $data['rating']
                                ]));
                            }
                        }
                    });
                    Notification::make()->title('Interview marked as completed')->success()->send();
                })
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['scheduled', 'confirmed'])),

            Actions\Action::make('reschedule')
                ->color('warning')
                ->icon('heroicon-o-calendar')
                ->form([
                    DateTimePicker::make('new_date')->required()->minDate(now()),
                    Textarea::make('reason')->required(),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $this->record->update([
                            'status' => 'rescheduled',
                            'scheduled_at' => $data['new_date'],
                            'cancellation_reason' => $data['reason'],
                        ]);

                        if ($candidate = $this->record->jobApplication?->candidate) {
                            $candidate->notify(new RecruitmentNotification('interview_rescheduled', [
                                'job_title' => $this->record->jobApplication->jobPosting->title,
                                'interview_date' => $data['new_date']->toDateString(),
                                'interview_time' => $data['new_date']->format('H:i'),
                                'reason' => $data['reason']
                            ]));
                        }
                    });
                    Notification::make()->title('Interview rescheduled')->success()->send();
                })
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['scheduled', 'confirmed'])),

            Actions\Action::make('cancel')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->form([
                    Textarea::make('reason')->required(),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $this->record->update([
                            'status' => 'cancelled',
                            'cancellation_reason' => $data['reason'],
                        ]);

                        if ($this->record->jobApplication) {
                            $this->record->jobApplication->update(['status' => 'interview_cancelled']);

                            if ($candidate = $this->record->jobApplication->candidate) {
                                $candidate->update(['status' => 'rejected']);
                                $candidate->notify(new RecruitmentNotification('interview_cancelled', [
                                    'job_title' => $this->record->jobApplication->jobPosting->title,
                                    'reason' => $data['reason']
                                ]));
                            }
                        }
                    });
                    Notification::make()->title('Interview cancelled')->success()->send();
                })
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['scheduled', 'confirmed']))
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Interview Overview')
                ->schema([
                    Infolists\Components\TextEntry::make('type')
                        ->badge(),

                    Infolists\Components\TextEntry::make('round_number')
                        ->label('Interview Round'),

                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'scheduled' => 'warning',
                            'confirmed' => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            'rescheduled' => 'warning',
                            'no_show' => 'danger',
                            default => 'gray',
                        }),

                    Infolists\Components\TextEntry::make('scheduled_at')
                        ->label('Date & Time')
                        ->dateTime(),
                ])
                ->columns(4),

            Infolists\Components\Grid::make(2)
                ->schema([
                    Infolists\Components\Section::make('Candidate Information')
                        ->schema([
                            Infolists\Components\TextEntry::make('jobApplication.candidate.full_name')
                                ->label('Candidate Name')
                                ->weight(FontWeight::Bold),

                            Infolists\Components\TextEntry::make('jobApplication.candidate.email')
                                ->label('Email')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('jobApplication.candidate.phone')
                                ->label('Phone')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('jobApplication.application_number')
                                ->label('Application Reference')
                                ->url(fn ($record) => JobApplicationResource::getUrl('view', ['record' => $record->jobApplication]))
                                ->openUrlInNewTab(),
                        ]),

                    Infolists\Components\Section::make('Interview Details')
                        ->schema([
                            Infolists\Components\TextEntry::make('mode')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'in_person' => 'success',
                                    'video' => 'info',
                                    'phone' => 'warning',
                                }),

                            Infolists\Components\TextEntry::make('location')
                                ->label(fn ($record) => match ($record->mode) {
                                    'in_person' => 'Location',
                                    'video' => 'Meeting Link',
                                    'phone' => 'Phone Number',
                                }),

                            Infolists\Components\TextEntry::make('duration_minutes')
                                ->label('Duration')
                                ->formatStateUsing(fn (string $state): string => "{$state} minutes"),

                            Infolists\Components\TextEntry::make('interviewer.name')
                                ->label('Interviewer'),
                        ]),
                ]),

            Infolists\Components\Section::make('Interview Questions')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('interview_questions')
                        ->schema([
                            Infolists\Components\TextEntry::make('question'),
                            Infolists\Components\TextEntry::make('category')
                                ->badge(),
                            Infolists\Components\TextEntry::make('notes')
                                ->markdown(),
                        ])
                        ->columns(3),
                ])
                ->collapsible()
                ->collapsed(false),

            Infolists\Components\Section::make('Notes & Instructions')
                ->schema([
                    Infolists\Components\TextEntry::make('notes')
                        ->markdown(),
                ])
                ->visible(fn ($record) => !empty($record->notes))
                ->collapsible(),

            Infolists\Components\Section::make('Interview Results')
                ->schema([
                    Infolists\Components\TextEntry::make('feedback')
                        ->markdown()
                        ->columnSpanFull(),

                    Infolists\Components\TextEntry::make('rating')
                        ->numeric(
                            decimalPlaces: 1,
                            decimalSeparator: '.',
                            thousandsSeparator: ',',
                        ),

                    Infolists\Components\TextEntry::make('recommendations')
                        ->markdown()
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => $record->status === 'completed')
                ->collapsible(),

            Infolists\Components\Section::make('Cancellation Details')
                ->schema([
                    Infolists\Components\TextEntry::make('cancellation_reason')
                        ->markdown(),
                ])
                ->visible(fn ($record) => in_array($record->status, ['cancelled', 'rescheduled']))
                ->collapsible(),
        ]);
    }
}
