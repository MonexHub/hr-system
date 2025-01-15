<?php

namespace App\Filament\Employee\Resources\JobPostingResource\Pages;

use App\Filament\Employee\Resources\JobPostingResource;
use App\Filament\Employee\Widgets\SimilarJobs;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Infolists\Components\Actions\Action as InfolistAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Support\Enums\FontWeight;

class ViewJobPosting extends ViewRecord
{
    protected static string $resource = JobPostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply')
                ->label('Apply Now')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->button()
                ->size('lg')
//                ->url(fn() => route('filament.filament.resources.job-postings.apply', ['record' => $this->record]))
                ->visible(fn() =>
                    !$this->record->hasApplied(auth()->user()?->employee) &&
                    $this->record->isOpen()
                ),

            Action::make('share')
                ->icon('heroicon-o-share')
                ->button()
                ->action(function () {
                    // Generate shareable link or share on social media
                }),

            Action::make('save')
                ->icon('heroicon-o-bookmark')
                ->button()
                ->color('gray')
                ->action(function () {
                }),
        ];
    }

    public function infolist(\Filament\Infolists\Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Job Overview')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('department.name')
                                    ->icon('heroicon-m-building-office')
                                    ->label('Department')
                                    ->placeholder('N/A'),

                                TextEntry::make('employment_type')
                                    ->icon('heroicon-m-briefcase')
                                    ->badge()
                                    ->placeholder('N/A'),
                                TextEntry::make('location')
                                    ->icon('heroicon-m-map-pin')
                                    ->suffixAction(
                                        fn ($record) => $record->is_remote
                                            ? InfolistAction::make('remote')
                                                ->icon('heroicon-m-globe-alt')
                                                ->label('Remote Available')
                                                ->color('success')
                                            : null
                                    ),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('salary_range')
                                    ->icon('heroicon-m-currency-dollar')
                                    ->placeholder('Not specified'),

                                TextEntry::make('closing_date')
                                    ->icon('heroicon-m-calendar')
                                    ->formatStateUsing(function ($state) {
                                        if ($state instanceof \Carbon\Carbon) {
                                            return $state->format('M d, Y');
                                        }
                                        return 'N/A';
                                    })
                                    ->color(fn ($record) =>
                                    $record->closing_date?->isPast() ? 'danger' : 'success'
                                    ),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Job Details')
                    ->description('Detailed information about the position')
                    ->schema([
                        TextEntry::make('description')
                            ->html()
                            ->columnSpanFull()
                            ->default('No description available'),

                        TextEntry::make('requirements')
                            ->label('Key Requirements')
                            ->state(function ($record) {
                                if (is_array($record->requirements)) {
                                    return collect($record->requirements)
                                        ->pluck('requirement')
                                        ->implode(', ');
                                }
                                return 'No requirements specified';
                            })
                            ->columnSpanFull(),

                        TextEntry::make('responsibilities')
                            ->label('Key Responsibilities')
                            ->state(function ($record) {
                                if (is_array($record->responsibilities)) {
                                    return collect($record->responsibilities)
                                        ->pluck('responsibility')
                                        ->implode(', ');
                                }
                                return 'No responsibilities specified';
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Qualifications & Skills')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('minimum_years_experience')
                                    ->label('Required Experience')
                                    ->formatStateUsing(fn($state) => $state ? $state . ' years minimum' : 'Not specified'),

                                TextEntry::make('education_level')
                                    ->label('Required Education')
                                    ->default('Not specified'),
                            ]),

                        TextEntry::make('skills_required')
                            ->label('Skills')
                            ->state(function ($record) {
                                if (is_array($record->skills_required)) {
                                    return collect($record->skills_required)
                                        ->map(fn($skill) => is_array($skill) ? $skill['skill'] ?? '' : $skill)
                                        ->filter()
                                        ->implode(', ');
                                }
                                return 'No skills specified';
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Benefits & Perks')
                    ->schema([
                        TextEntry::make('benefits')
                            ->label('Benefits')
                            ->state(function ($record) {
                                if (is_array($record->benefits)) {
                                    return collect($record->benefits)
                                        ->map(fn($benefit) => is_array($benefit) ? $benefit['benefit'] ?? '' : $benefit)
                                        ->filter()
                                        ->implode(', ');
                                }
                                return 'No benefits specified';
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => !empty($record->benefits)),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('positions_available')
                            ->label('Number of Positions')
                            ->default('Not specified')
                            ->suffix(fn ($record) =>
                            $record->positions_filled ? " ({$record->positions_filled} filled)" : ''
                            ),

                        TextEntry::make('reference_code')
                            ->label('Job Reference')
                            ->default('N/A')
                            ->copyable()
                            ->copyMessage('Reference code copied'),

                        TextEntry::make('created_at')
                            ->label('Posted on')
                            ->date()
                            ->default('N/A'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    protected function getFooterWidgets(): array
    {
        return [
//            SimilarJobs::class,
//            Widgets\DepartmentInfo::class,
        ];
    }

    protected function mutateFormData($data): array
    {
        $data = parent::mutateFormData($data);

        // Ensure requirements and responsibilities are always arrays
        $data['requirements'] = is_string($data['requirements'])
            ? json_decode($data['requirements'], true)
            : (is_array($data['requirements'])
                ? $data['requirements']
                : []);

        $data['responsibilities'] = is_string($data['responsibilities'])
            ? json_decode($data['responsibilities'], true)
            : (is_array($data['responsibilities'])
                ? $data['responsibilities']
                : []);

        return $data;
    }

    public function getTitle(): string
    {
        return $this->record->title;
    }

    public function getSubheading(): ?string
    {
        return "Reference: {$this->record->reference_code}";
    }
}
