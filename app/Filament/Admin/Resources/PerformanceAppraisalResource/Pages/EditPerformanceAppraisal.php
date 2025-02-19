<?php

namespace App\Filament\Admin\Resources\PerformanceAppraisalResource\Pages;

use App\Filament\Admin\Resources\PerformanceAppraisalResource;
use App\Helpers\AppraisalPeriodHelper;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;

class EditPerformanceAppraisal extends EditRecord
{
    protected static string $resource = PerformanceAppraisalResource::class;

    protected function getTableActions(): array
    {
        return [
            // View Action
            ViewAction::make(),

            // Submit Action
            Action::make('submit')
                ->action(fn (Model $record) => $record->submit())
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (Model $record): bool => $record->status === 'draft')
                ->modalHeading('Submit Appraisal')
                ->modalSubheading('Are you sure you want to submit this appraisal? It will be sent to the supervisor for review.'),

            // Review Action
            Action::make('review')
                ->action(fn (Model $record) => $record->review())
                ->requiresConfirmation()
                ->color('primary')
                ->icon('heroicon-o-eye')
                ->visible(fn (Model $record): bool => $record->status === 'submitted' && auth()->user()->id === $record->supervisor_id)
                ->modalHeading('Review Appraisal')
                ->modalSubheading('Start reviewing this appraisal?'),

            // Complete Action
            Action::make('complete')
                ->action(fn (Model $record) => $record->complete())
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (Model $record): bool => $record->status === 'reviewed' && auth()->user()->id === $record->supervisor_id)
                ->modalHeading('Complete Appraisal')
                ->modalSubheading('Are you sure you want to complete this appraisal? This action cannot be undone.'),

            // Request Changes Action
            Action::make('requestChanges')
                ->action(fn (Model $record) => $record->requestChanges())
                ->requiresConfirmation()
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn (Model $record): bool => $record->status === 'reviewed' && auth()->user()->id === $record->supervisor_id)
                ->modalHeading('Request Changes')
                ->modalSubheading('Request changes to this appraisal? It will be returned to draft status.'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
              selectFilter::make('status')
                ->options([
                    'draft' => 'Draft',
                    'submitted' => 'Submitted',
                    'reviewed' => 'Under Review',
                    'completed' => 'Completed',
                    'rejected' => 'Rejected'
                ]),

                 SelectFilter::make('appraisal_period')
                ->options(fn () => AppraisalPeriodHelper::generatePeriods()),
        ];
    }
}
