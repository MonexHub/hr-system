<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

                    $this->notification()->success('Candidate status updated successfully');
                }),
        ];
    }
}
