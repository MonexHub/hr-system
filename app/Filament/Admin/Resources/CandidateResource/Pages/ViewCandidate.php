<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use App\Notifications\RecruitmentNotification;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewCandidate extends ViewRecord
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('download_resume')
                ->url(fn () => $this->record->resume_path ? Storage::url($this->record->resume_path) : null)
                ->hidden(fn () => !$this->record->resume_path)
                ->icon('heroicon-o-cloud-arrow-down')
                ->openUrlInNewTab(),
            Actions\Action::make('change_status')
                ->form([
                    Select::make('status')
                        ->options([
                            'applied' => 'Applied',
                            'screening' => 'Screening',
                            'shortlisted' => 'Shortlisted',
                            'interview' => 'Interview Stage',
                            'offer' => 'Offer Stage',
                            'hired' => 'Hired',
                            'rejected' => 'Rejected',
                            'withdrawn' => 'Withdrawn'
                        ])
                        ->required(),
                    Textarea::make('notes')
                        ->label('Status Change Notes'),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => $data['status'],
                    ]);

                    // Send notification based on status
                    $this->record->candidate->notify(new RecruitmentNotification($data['status'], [
                        'job_title' => $this->record->jobPosting->title,
                        'application_id' => $this->record->id,
                        'notes' => $data['notes'],
                        'interview_date' => $data['status'] === 'interview' ? now()->addDays(7)->toDateString() : null,
                        'interview_time' => $data['status'] === 'interview' ? '10:00' : null,
                        'start_date' => $data['status'] === 'offer' ? now()->addDays(30)->format('Y-m-d') : null,
                        'valid_until' => $data['status'] === 'offer' ? now()->addDays(7)->format('Y-m-d') : null
                    ]));

                    Notification::make()
                        ->title('Candidate status updated successfully')
                        ->success()
                        ->send();
                }),
        ];
    }
}
