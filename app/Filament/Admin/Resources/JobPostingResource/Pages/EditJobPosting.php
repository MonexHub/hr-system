<?php

namespace App\Filament\Admin\Resources\JobPostingResource\Pages;

use App\Filament\Admin\Resources\JobPostingResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditJobPosting extends EditRecord
{
    protected static string $resource = JobPostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => in_array($this->record->status, ['draft', 'cancelled'])),

            Actions\Action::make('submitForApproval')
                ->action(function () {
                    $this->record->update([
                        'status' => 'pending_approval',
                    ]);

                    Notification::make()
                        ->title('Submitted for approval')
                        ->success()
                        ->send();
                })
                ->visible(fn() => $this->record->status === 'draft')
                ->color('warning')
                ->icon('heroicon-o-paper-airplane'),

            Actions\Action::make('approve')
                ->action(function () {
                    $this->record->update([
                        'status' => 'published',
                        'publishing_date' => now(),
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Job posting approved and published')
                        ->success()
                        ->send();
                })
                ->visible(fn() => $this->record->status === 'pending_approval' && auth()->user()->can('approve_job_postings'))
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->requiresConfirmation(),

            Actions\Action::make('reject')
                ->action(function () {
                    $this->record->update([
                        'status' => 'draft',
                    ]);

                    Notification::make()
                        ->title('Job posting returned to draft')
                        ->warning()
                        ->send();
                })
                ->visible(fn() => $this->record->status === 'pending_approval' && auth()->user()->can('approve_job_postings'))
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation(),

            Actions\Action::make('publish')
                ->action(function () {
                    if ($this->record->positions_available <= 0) {
                        Notification::make()
                            ->title('Cannot publish')
                            ->body('Number of available positions must be greater than zero.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $this->record->update([
                        'status' => 'published',
                        'publishing_date' => now(),
                    ]);

                    Notification::make()
                        ->title('Job posting published successfully')
                        ->success()
                        ->send();
                })
                ->visible(fn() => in_array($this->record->status, ['draft', 'pending_approval']) &&
                    auth()->user()->can('publish_job_postings'))
                ->color('success')
                ->icon('heroicon-o-globe-alt')
                ->requiresConfirmation(),

            Actions\Action::make('close')
                ->action(function () {
                    $this->record->update([
                        'status' => 'closed',
                    ]);

                    Notification::make()
                        ->title('Job posting closed successfully')
                        ->success()
                        ->send();
                })
                ->visible(fn() => $this->record->status === 'published')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation(),

            Actions\Action::make('reopen')
                ->action(function () {
                    if ($this->record->positions_filled >= $this->record->positions_available) {
                        Notification::make()
                            ->title('Cannot reopen')
                            ->body('All positions have been filled.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $this->record->update([
                        'status' => 'published',
                        'publishing_date' => now(),
                    ]);

                    Notification::make()
                        ->title('Job posting reopened successfully')
                        ->success()
                        ->send();
                })
                ->visible(fn() => $this->record->status === 'closed')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation(),

            Actions\Action::make('markAsFilled')
                ->action(function () {
                    $this->record->update([
                        'status' => 'filled',
                        'positions_filled' => $this->record->positions_available
                    ]);

                    Notification::make()
                        ->title('Job posting marked as filled')
                        ->success()
                        ->send();
                })
                ->visible(fn() => in_array($this->record->status, ['published', 'closed']))
                ->color('info')
                ->icon('heroicon-o-user-group')
                ->requiresConfirmation(),

            Actions\Action::make('cancel')
                ->action(function () {
                    $this->record->update([
                        'status' => 'cancelled'
                    ]);

                    Notification::make()
                        ->title('Job posting cancelled')
                        ->success()
                        ->send();
                })
                ->visible(fn() => !in_array($this->record->status, ['filled', 'cancelled']))
                ->color('danger')
                ->icon('heroicon-o-no-symbol')
                ->requiresConfirmation(),
        ];
    }
}
