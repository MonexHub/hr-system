<?php

namespace App\Filament\Admin\Resources\JobOfferResource\Pages;

use App\Filament\Admin\Resources\JobOfferResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewJobOffer extends ViewRecord
{
    protected static string $resource = JobOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);
                    $this->notification()->success('Offer approved successfully');
                })
                ->visible(fn () => $this->record->status === 'pending_approval'),

            Actions\Action::make('send_offer')
                ->icon('heroicon-o-paper-airplane')
                ->action(function () {
                    $this->record->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                    $this->notification()->success('Offer sent to candidate');
                })
                ->visible(fn () => $this->record->status === 'approved'),

            Actions\Action::make('mark_accepted')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    DatePicker::make('acceptance_date')
                        ->required()
                        ->default(now()),
                    Textarea::make('notes')
                        ->label('Acceptance Notes'),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'accepted',
                        'responded_at' => $data['acceptance_date'],
                        'internal_notes' => $data['notes'],
                    ]);
                    $this->notification()->success('Offer marked as accepted');
                })
                ->visible(fn () => $this->record->status === 'sent'),

            Actions\Action::make('mark_rejected')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('rejection_reason')
                        ->required()
                        ->label('Reason for Rejection'),
                    DatePicker::make('rejection_date')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                        'responded_at' => $data['rejection_date'],
                    ]);
                    $this->notification()->success('Offer marked as rejected');
                })
                ->visible(fn () => in_array($this->record->status, ['sent', 'negotiating'])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // Offer Overview Section
            Infolists\Components\Section::make('Offer Overview')
                ->schema([
                    Infolists\Components\TextEntry::make('offer_number')
                        ->label('Reference')
                        ->copyable(),

                    Infolists\Components\TextEntry::make('created_at')
                        ->dateTime(),

                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'draft' => 'gray',
                            'pending_approval' => 'warning',
                            'approved' => 'success',
                            'sent' => 'info',
                            'accepted' => 'success',
                            'negotiating' => 'warning',
                            'rejected' => 'danger',
                            'expired' => 'danger',
                            default => 'gray',
                        }),

                    Infolists\Components\TextEntry::make('valid_until')
                        ->date()
                        ->weight(FontWeight::Bold)
                        ->color(fn ($record) => now() > $record->valid_until ? 'danger' : 'success'),
                ])
                ->columns(4),

            // Candidate and Position Details
            Infolists\Components\Grid::make(2)
                ->schema([
                    Infolists\Components\Section::make('Candidate Information')
                        ->schema([
                            Infolists\Components\TextEntry::make('jobApplication.candidate.full_name')
                                ->label('Name')
                                ->weight(FontWeight::Bold),

                            Infolists\Components\TextEntry::make('jobApplication.candidate.email')
                                ->label('Email')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('jobApplication.candidate.phone')
                                ->label('Phone')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('jobApplication.application_number')
                                ->label('Application Reference'),
                        ]),

                    Infolists\Components\Section::make('Position Details')
                        ->schema([
                            Infolists\Components\TextEntry::make('jobApplication.jobPosting.title')
                                ->label('Position'),

                            Infolists\Components\TextEntry::make('jobApplication.jobPosting.department.name')
                                ->label('Department'),

                            Infolists\Components\TextEntry::make('proposed_start_date')
                                ->label('Start Date')
                                ->date(),

                            Infolists\Components\TextEntry::make('jobApplication.jobPosting.location')
                                ->label('Location'),
                        ]),
                ]),

            // Compensation Details
            Infolists\Components\Section::make('Compensation Package')
                ->schema([
                    Infolists\Components\Grid::make(3)
                        ->schema([
                            Infolists\Components\TextEntry::make('base_salary')
                                ->formatStateUsing(fn ($record) => match($record->salary_currency) {
                                    'TZS' => number_format($record->base_salary, 0) . ' TSh',
                                    'USD' => '$' . number_format($record->base_salary, 2),
                                    'EUR' => '€' . number_format($record->base_salary, 2),
                                    'GBP' => '£' . number_format($record->base_salary, 2),
                                    'KES' => 'KSh ' . number_format($record->base_salary, 2),
                                    'UGX' => 'USh ' . number_format($record->base_salary, 0),
                                    'RWF' => 'RF ' . number_format($record->base_salary, 0),
                                    default => number_format($record->base_salary, 0) . ' TSh',
                                })
                                ->label('Base Salary'),

                            Infolists\Components\TextEntry::make('salary_currency')
                                ->badge()
                                ->label('Currency'),
                        ]),

                    Infolists\Components\RepeatableEntry::make('benefits_package')
                        ->schema([
                            Infolists\Components\TextEntry::make('benefit')
                                ->label('Benefit'),
                            Infolists\Components\TextEntry::make('value'),
                            Infolists\Components\TextEntry::make('description')
                                ->markdown(),
                        ])
                        ->columns(3),

                    Infolists\Components\RepeatableEntry::make('additional_allowances')
                        ->schema([
                            Infolists\Components\TextEntry::make('allowance')
                                ->label('Type'),
                            Infolists\Components\TextEntry::make('amount')
                                ->money($this->record->salary_currency),
                            Infolists\Components\TextEntry::make('frequency'),
                        ])
                        ->columns(3),
                ])
                ->collapsible(),

            // Terms and Conditions
            Infolists\Components\Section::make('Terms & Conditions')
                ->schema([
                    Infolists\Components\TextEntry::make('additional_terms')
                        ->markdown()
                        ->columnSpanFull(),

                    Infolists\Components\TextEntry::make('special_conditions')
                        ->markdown()
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            // Response Details (visible based on status)
            Infolists\Components\Section::make('Response Details')
                ->schema([
                    Infolists\Components\TextEntry::make('responded_at')
                        ->label('Response Date')
                        ->dateTime(),

                    Infolists\Components\TextEntry::make('rejection_reason')
                        ->markdown()
                        ->visible(fn ($record) => $record->status === 'rejected'),

                    Infolists\Components\TextEntry::make('negotiation_history')
                        ->listWithLineBreaks()
                        ->visible(fn ($record) => $record->status === 'negotiating'),
                ])
                ->visible(fn ($record) => in_array($record->status, ['accepted', 'rejected', 'negotiating']))
                ->collapsible(),

            // Approval Information
            Infolists\Components\Section::make('Approval Information')
                ->schema([
                    Infolists\Components\TextEntry::make('approved_by_user.name')
                        ->label('Approved By'),

                    Infolists\Components\TextEntry::make('approved_at')
                        ->label('Approval Date')
                        ->dateTime(),

                    Infolists\Components\TextEntry::make('sent_at')
                        ->label('Sent on')
                        ->dateTime(),

                    Infolists\Components\TextEntry::make('internal_notes')
                        ->markdown(),
                ])
                ->columns(4)
                ->visible(fn ($record) => $record->approved_at !== null)
                ->collapsible(),
        ]);
    }
}
