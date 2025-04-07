<?php

namespace App\Filament\Admin\Resources\LeaveRequestResource\Pages;

use App\Filament\Admin\Resources\LeaveRequestResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use App\Models\LeaveRequest;
use Filament\Notifications\Notification;
use Filament\Forms;

class ViewLeaveRequest extends ViewRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            // Edit Action - only for pending requests
            Actions\EditAction::make()
                ->visible(fn (LeaveRequest $record): bool =>
                    $record->status === LeaveRequest::STATUS_PENDING &&
                    $record->employee_id === auth()->user()->employee?->id
                ),

            // Department Head Approval Action
            Actions\Action::make('approve_department')
                ->label('Approve (HOD)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Forms\Components\Textarea::make('remarks')
                        ->label('Approval Remarks')
                        ->required(),
                ])
                ->visible(fn (LeaveRequest $record): bool =>
                    $record->status === LeaveRequest::STATUS_PENDING &&
                    (auth()->user()->hasRole('department_head') || auth()->user()->hasRole('super_admin'))
                )
                ->action(function (array $data): void {
                    try {
                        // Dispatch the job to process the approval asynchronously
                        \App\Jobs\ProcessLeaveApproval::dispatch(
                            $this->record->id,
                            auth()->id(),
                            $data['remarks'],
                            'department'
                        );

                        Notification::make()
                            ->title('Processing')
                            ->body('Leave request is being processed for approval. You will be notified when complete.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to queue approval. ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // HR Approval Action
            Actions\Action::make('approve_hr')
                ->label('Approve (HR)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Forms\Components\Textarea::make('remarks')
                        ->label('Approval Remarks')
                        ->required(),
                ])
                ->visible(fn (LeaveRequest $record): bool =>
                    $record->status === LeaveRequest::STATUS_DEPARTMENT_APPROVED &&
                    (auth()->user()->hasRole('hr_manager') || auth()->user()->hasRole('super_admin'))
                )
                ->action(function (array $data): void {
                    try {
                        // Dispatch the job to process the approval asynchronously
                        \App\Jobs\ProcessLeaveApproval::dispatch(
                            $this->record->id,
                            auth()->id(),
                            $data['remarks'],
                            'hr'
                        );

                        Notification::make()
                            ->title('Processing')
                            ->body('Leave request is being processed for HR approval. You will be notified when complete.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to queue approval. ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // CEO Approval Action
            Actions\Action::make('approve_ceo')
                ->label('Approve (CEO)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Forms\Components\Textarea::make('remarks')
                        ->label('Approval Remarks')
                        ->required(),
                ])
                ->visible(fn (LeaveRequest $record): bool =>
                    $record->status === LeaveRequest::STATUS_HR_APPROVED &&
                    $record->isEmployeeDepartmentHead() &&
                    (auth()->user()->hasRole('chief_executive_officer') || auth()->user()->hasRole('super_admin'))
                )
                ->action(function (array $data): void {
                    try {
                        // Dispatch the job to process the approval asynchronously
                        \App\Jobs\ProcessLeaveApproval::dispatch(
                            $this->record->id,
                            auth()->id(),
                            $data['remarks'],
                            'ceo'
                        );

                        Notification::make()
                            ->title('Processing')
                            ->body('Leave request is being processed for CEO approval. You will be notified when complete.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to queue approval. ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Reject Action
            Actions\Action::make('reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label('Rejection Reason')
                        ->required(),
                ])
                ->visible(fn (LeaveRequest $record): bool =>
                    in_array($record->status, [
                        LeaveRequest::STATUS_PENDING,
                        LeaveRequest::STATUS_DEPARTMENT_APPROVED,
                        LeaveRequest::STATUS_HR_APPROVED
                    ]) &&
                    (
                        auth()->user()->hasRole('super_admin') ||
                        (
                            auth()->user()->hasRole('department_head') &&
                            $record->status === LeaveRequest::STATUS_PENDING
                        ) ||
                        (
                            auth()->user()->hasRole('hr_manager') &&
                            $record->status === LeaveRequest::STATUS_DEPARTMENT_APPROVED
                        ) ||
                        (
                            auth()->user()->hasRole('chief_executive_officer') &&
                            $record->status === LeaveRequest::STATUS_HR_APPROVED &&
                            $record->isEmployeeDepartmentHead()
                        )
                    )
                )
                ->action(function (array $data): void {
                    try {
                        // Dispatch the job to process the rejection asynchronously
                        \App\Jobs\ProcessLeaveApproval::dispatch(
                            $this->record->id,
                            auth()->id(),
                            $data['reason'],
                            'reject'
                        );

                        Notification::make()
                            ->title('Processing')
                            ->body('Leave request is being processed for rejection. You will be notified when complete.')
                            ->warning()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to queue rejection. ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Cancel Action
            Actions\Action::make('cancel')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label('Cancellation Reason')
                        ->required(),
                ])
                ->visible(fn (LeaveRequest $record): bool =>
                    in_array($record->status, [
                        LeaveRequest::STATUS_PENDING,
                        LeaveRequest::STATUS_DEPARTMENT_APPROVED
                    ]) &&
                    (
                        auth()->user()->hasRole('super_admin') ||
                        $record->employee_id === auth()->user()->employee->id
                    )
                )
                ->action(function (array $data): void {
                    try {
                        // Dispatch the job to process the cancellation asynchronously
                        \App\Jobs\ProcessLeaveApproval::dispatch(
                            $this->record->id,
                            auth()->id(),
                            $data['reason'],
                            'cancel'
                        );

                        Notification::make()
                            ->title('Processing')
                            ->body('Leave request is being processed for cancellation. You will be notified when complete.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to queue cancellation. ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
