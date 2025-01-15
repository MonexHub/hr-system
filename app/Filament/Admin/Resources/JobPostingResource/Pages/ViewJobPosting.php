<?php

namespace App\Filament\Admin\Resources\JobPostingResource\Pages;

use App\Filament\Admin\Resources\JobPostingResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewJobPosting extends ViewRecord
{
    protected static string $resource = JobPostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('publish')
                ->action(fn () => $this->record->update(['status' => 'published', 'publishing_date' => now()]))
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-globe-alt')
                ->visible(fn () => $this->record->status === 'draft'),

            Actions\Action::make('close')
                ->action(fn () => $this->record->update(['status' => 'closed']))
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => $this->record->status === 'published'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Job Overview')
                    ->schema([
                        Infolists\Components\TextEntry::make('position_code')
                            ->label('Reference')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('title')
                            ->label('Position Title')
                            ->weight(FontWeight::Bold),

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

                        Infolists\Components\TextEntry::make('department.name')
                            ->label('Department'),
                    ])
                    ->columns(4),

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
                            ->formatStateUsing(fn (bool $state) => $state ? 'Remote Available' : 'Office Based'),
                    ])
                    ->columns(3),

                Infolists\Components\Grid::make(3)
                    ->schema([
                        Infolists\Components\Section::make('Requirements')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('requirements')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('requirement')
                                    ])
                                    ->columnSpanFull(),
                            ]),

                        Infolists\Components\Section::make('Responsibilities')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('responsibilities')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('responsibility')
                                    ])
                                    ->columnSpanFull(),
                            ]),

                        Infolists\Components\Section::make('Benefits')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('benefits')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('benefit')
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Salary & Position Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('salary_range')
                            ->visible(fn () => !$this->record->hide_salary),

                        Infolists\Components\TextEntry::make('positions_available')
                            ->label('Openings'),

                        Infolists\Components\TextEntry::make('positions_filled')
                            ->label('Positions Filled'),

                        Infolists\Components\TextEntry::make('minimum_years_experience')
                            ->label('Min. Experience (Years)'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Applications Overview')
                    ->schema([
                        Infolists\Components\TextEntry::make('applications_count')
                            ->label('Total Applications')
                            ->counts('applications'),

                        Infolists\Components\TextEntry::make('shortlisted_count')
                            ->label('Shortlisted')
                            ->counts('applications', fn ($query) => $query->where('status', 'shortlisted')),

                        Infolists\Components\TextEntry::make('interviewed_count')
                            ->label('Interviewed')
                            ->counts('applications', fn ($query) => $query->whereIn('status', ['interview_scheduled', 'interview_completed'])),

                        Infolists\Components\TextEntry::make('hired_count')
                            ->label('Hired')
                            ->counts('applications', fn ($query) => $query->where('status', 'hired')),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Publishing Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('publishing_date')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('closing_date')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('created_by_user.name')
                            ->label('Created By'),

                        Infolists\Components\TextEntry::make('approved_by_user.name')
                            ->label('Approved By'),
                    ])
                    ->columns(4),
            ]);
    }
}
