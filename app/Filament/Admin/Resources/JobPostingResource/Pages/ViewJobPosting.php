<?php

namespace App\Filament\Admin\Resources\JobPostingResource\Pages;

use App\Filament\Admin\Resources\JobPostingResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;

class ViewJobPosting extends ViewRecord
{
    protected static string $resource = JobPostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () =>
                    in_array($this->record->status, ['draft', 'pending_approval']) &&
                    auth()->user()->hasRole('hr_manager')),

            Actions\Action::make('approve_publish')
                ->action(fn () => $this->record->approve(auth()->id()))
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () =>
                    $this->record->status === 'pending_approval' &&
                    auth()->user()->hasRole('hr_manager')),

            Actions\Action::make('reject')
                ->form([
                    Textarea::make('rejection_reason')
                        ->required()
                        ->label('Reason for Rejection'),
                ])
                ->action(fn (array $data) => $this->record->reject())
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () =>
                    $this->record->status === 'pending_approval' &&
                    auth()->user()->hasRole('hr_manager')),

            Actions\Action::make('close')
                ->action(fn () => $this->record->close())
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-lock-closed')
                ->visible(fn () =>
                    $this->record->status === 'published' &&
                    auth()->user()->hasRole('hr_manager')),

            Actions\Action::make('mark_filled')
                ->action(fn () => $this->record->markAsFilled())
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-user-group')
                ->visible(fn () =>
                    $this->record->status === 'published' &&
                    auth()->user()->hasRole('hr_manager')),

            Actions\Action::make('download_document')
                ->url(fn () => Storage::url($this->record->document_path))
                ->icon('heroicon-o-document-arrow-down')
                ->openUrlInNewTab()
                ->visible(fn () =>
                    $this->record->is_document_based &&
                    $this->record->document_path),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Job Overview
                Infolists\Components\Section::make('Job Overview')
                    ->schema([
                        Infolists\Components\TextEntry::make('position_code')
                            ->label('Reference')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('title')
                            ->label('Position Title')
                            ->weight(FontWeight::Bold),

                        Infolists\Components\TextEntry::make('department.name')
                            ->label('Department'),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'pending_approval' => 'warning',
                                'published' => 'success',
                                'closed' => 'danger',
                                'cancelled' => 'danger',
                                'filled' => 'info',
                                default => 'gray',
                            }),
                    ])
                    ->columns(4),

                // Job Details
                Infolists\Components\Section::make('Job Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->markdown()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('employment_type')
                            ->badge(),

                        Infolists\Components\TextEntry::make('location')
                            ->icon('heroicon-m-map-pin'),

                        Infolists\Components\TextEntry::make('is_remote')
                            ->label('Remote Work')
                            ->badge()
                            ->color('success')
                            ->formatStateUsing(fn (bool $state) =>
                            $state ? 'Remote Available' : 'Office Based'),
                    ])
                    ->columns(3),

                // Applications Overview
                Infolists\Components\Section::make('Applications Overview')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_applications')
                            ->label('Total Applications')
                            ->state(fn ($record) => $record->applications()->count()),

                        Infolists\Components\TextEntry::make('shortlisted')
                            ->label('Shortlisted')
                            ->state(fn ($record) => $record->applications()
                                ->where('status', 'shortlisted')
                                ->count()),

                        Infolists\Components\TextEntry::make('interviewed')
                            ->label('Interviewed')
                            ->state(fn ($record) => $record->applications()
                                ->whereIn('status', ['interview_scheduled', 'interview_completed'])
                                ->count()),

                        Infolists\Components\TextEntry::make('hired')
                            ->label('Hired')
                            ->state(fn ($record) => $record->applications()
                                ->where('status', 'hired')
                                ->count()),
                    ])
                    ->columns(4),

                // Position Details
                Infolists\Components\Section::make('Position Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('salary_range')
                            ->visible(fn () => !$this->record->hide_salary),

                        Infolists\Components\TextEntry::make('positions_available')
                            ->label('Openings'),

                        Infolists\Components\TextEntry::make('positions_filled')
                            ->label('Positions Filled'),
                    ])
                    ->columns(3),

                // Publishing Information
                Infolists\Components\Section::make('Publishing Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('publishing_date')
                            ->dateTime()
                            ->label('Publishing Date'),

                        Infolists\Components\TextEntry::make('closing_date')
                            ->dateTime()
                            ->label('Closing Date'),

                        Infolists\Components\TextEntry::make('creator.name')
                            ->label('Created By')
                            ->icon('heroicon-m-user'),

                        Infolists\Components\TextEntry::make('approver.name')
                            ->label('Approved By')
                            ->icon('heroicon-m-check-circle')
                            ->visible(fn ($record) => $record->approved_by !== null),

                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->label('Created On'),

                        Infolists\Components\TextEntry::make('approved_at')
                            ->dateTime()
                            ->label('Approved On')
                            ->visible(fn ($record) => $record->approved_at !== null),
                    ])
                    ->columns(3)
            ]);
    }
}
