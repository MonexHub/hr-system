<?php

namespace App\Filament\Admin\Resources\LeaveRequestResource\Pages;

use App\Filament\Admin\Resources\LeaveRequestResource;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;

class ViewLeaveRequest extends ViewRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Edit action - only for pending requests by the employee who created them
            Actions\EditAction::make()
                ->visible(fn () =>
                    $this->record->status === 'pending' &&
                    $this->record->employee_id === auth()->user()->employee?->id
                ),

            // Cancel action - only for employees canceling their own pending requests
            Actions\Action::make('cancel')
                ->label('Cancel Request')
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => 'cancelled']))
                ->visible(fn () =>
                    $this->record->status === 'pending' &&
                    $this->record->employee_id === auth()->user()->employee?->id
                ),

            // Manager approval - only for managers (not employees) reviewing their team's requests
            Actions\Action::make('manager_approve')
                ->label('Approve as Manager')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn () => $this->record->approveByManager(auth()->id()))
                ->visible(fn () =>
                    $this->record->status === 'pending' &&
                    $this->record->employee->reporting_to === auth()->user()->employee?->id &&
                    !auth()->user()->hasRole('employee')
                ),

            // HR approval - only for HR reviewing pending_hr requests
            Actions\Action::make('hr_approve')
                ->label('Approve as HR')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn () => $this->record->approveByHR(auth()->id()))
                ->visible(fn () =>
                    $this->record->status === 'pending_hr' &&
                    auth()->user()->hasRole('hr_manager')
                ),

            // Reject action - only for managers and HR
            Actions\Action::make('reject')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->form([
                    Textarea::make('rejection_reason')
                        ->required()
                        ->label('Reason for Rejection'),
                ])
                ->action(fn (array $data) => $this->record->reject(auth()->id(), $data['rejection_reason']))
                ->visible(fn () =>
                    ($this->record->status === 'pending' &&
                        $this->record->employee->reporting_to === auth()->user()->employee?->id &&
                        !auth()->user()->hasRole('employee')) ||
                    ($this->record->status === 'pending_hr' &&
                        auth()->user()->hasRole('hr_manager'))
                ),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Request Overview')
                    ->schema([
                        Infolists\Components\TextEntry::make('employee.full_name')
                            ->label('Employee')
                            ->weight(FontWeight::Bold),

                        Infolists\Components\TextEntry::make('leaveType.name')
                            ->label('Leave Type'),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'cancelled' => 'gray',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('days_taken')
                            ->label('Days Requested'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Leave Period')
                    ->schema([
                        Infolists\Components\TextEntry::make('start_date')
                            ->date(),

                        Infolists\Components\TextEntry::make('end_date')
                            ->date(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Request Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('reason')
                            ->markdown()
                            ->columnSpanFull(),

                        Infolists\Components\RepeatableEntry::make('attachments')
                            ->schema([
                                Infolists\Components\TextEntry::make('filename')
                                    ->url(fn ($record) => Storage::url($record)),
                            ])
                            ->visible(fn ($record) => !empty($record->attachments))
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Approval Information')
                    ->schema([
                        TextEntry::make('approver.name')
                            ->visible(fn ($record) => $record->status === 'approved'),

                        TextEntry::make('approved_at')
                            ->dateTime()
                            ->visible(fn ($record) => $record->status === 'approved'),

                        TextEntry::make('rejection_reason')
                            ->markdown()
                            ->visible(fn ($record) => $record->status === 'rejected')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => in_array($record->status, ['approved', 'rejected']))
                    ->columns(2),
            ]);
    }
}
