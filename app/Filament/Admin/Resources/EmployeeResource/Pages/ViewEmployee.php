<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('download_cv')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn ($record) => $record->cv ? storage::url($record->cv) : null)
                ->visible(fn ($record) => $record->cv)
                ->openUrlInNewTab(),

            Actions\Action::make('terminate')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                  DatePicker::make('termination_date')
                        ->required()
                        ->default(now()),
                    Textarea::make('termination_reason')
                        ->required(),
                ])
                ->action(function ($data) {
                    $this->record->update([
                        'employment_status' => 'terminated',
                        'contract_end_date' => $data['termination_date'],
                        'termination_reason' => $data['termination_reason'],
                    ]);
                })
                ->visible(fn ($record) => $record->employment_status === 'active'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Employee Overview')
                ->schema([
                    Infolists\Components\Split::make([
                        Infolists\Components\ImageEntry::make('profile_photo')
                            ->circular()
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name)),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('employee_code')
                                    ->label('Employee ID')
                                    ->weight(FontWeight::Bold),

                                Infolists\Components\TextEntry::make('full_name')
                                    ->label('Name')
                                    ->weight(FontWeight::Bold),

                                Infolists\Components\TextEntry::make('job_title')
                                    ->label('Position'),

                                Infolists\Components\TextEntry::make('department.name')
                                    ->label('Department'),

                                Infolists\Components\TextEntry::make('employment_status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'active' => 'success',
                                        'probation' => 'warning',
                                        'suspended' => 'danger',
                                        'terminated' => 'danger',
                                        'resigned' => 'gray',
                                        default => 'gray',
                                    }),

                                Infolists\Components\TextEntry::make('appointment_date')
                                    ->label('Joined Date')
                                    ->date(),
                            ]),
                    ])->from('md'),
                ]),

            Infolists\Components\Grid::make(3)
                ->schema([
                    Infolists\Components\Section::make('Personal Information')
                        ->schema([
                            Infolists\Components\TextEntry::make('gender'),
                            Infolists\Components\TextEntry::make('birthdate')->date(),
                            Infolists\Components\TextEntry::make('marital_status'),
                        ]),

                    Infolists\Components\Section::make('Contact Information')
                        ->schema([
                            Infolists\Components\TextEntry::make('email')->copyable(),
                            Infolists\Components\TextEntry::make('phone_number')->copyable(),
                            Infolists\Components\TextEntry::make('permanent_address'),
                            Infolists\Components\TextEntry::make('city'),
                            Infolists\Components\TextEntry::make('state'),
                            Infolists\Components\TextEntry::make('postal_code'),
                        ]),

                    Infolists\Components\Section::make('Emergency Contact')
                        ->schema([
                            Infolists\Components\TextEntry::make('emergency_contact_name'),
                            Infolists\Components\TextEntry::make('emergency_contact_phone')
                                ->copyable(),
                        ]),
                ]),

            Infolists\Components\Section::make('Employment Details')
                ->schema([
                    Infolists\Components\Grid::make(3)
                        ->schema([
                            Infolists\Components\TextEntry::make('contract_type')
                                ->badge(),

                            Infolists\Components\TextEntry::make('appointment_date')
                                ->label('Start Date')
                                ->date(),

                            Infolists\Components\TextEntry::make('contract_end_date')
                                ->label('End Date')
                                ->date()
                                ->visible(fn ($record) => $record->contract_type !== 'permanent'),

                            Infolists\Components\TextEntry::make('salary')
                                ->money('TZS'),

                            Infolists\Components\TextEntry::make('reportingTo.full_name')
                                ->label('Reports To'),
                        ]),
                ]),

            Infolists\Components\Section::make('Documents & Identifications')
                ->schema([
                    Infolists\Components\Grid::make(3)
                        ->schema([
                            Infolists\Components\TextEntry::make('nssf_number')
                                ->label('NSSF Number')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('bank_account')
                                ->label('Bank Account')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('bank_name'),

                            Infolists\Components\TextEntry::make('cv')
                                ->url(fn ($record) => $record->cv ? storage::url($record->cv) : null)
                                ->openUrlInNewTab()
                                ->label('CV/Resume'),

                            Infolists\Components\TextEntry::make('id_proof')
                                ->url(fn ($record) => $record->id_proof ? storage::url($record->id_proof) : null)
                                ->openUrlInNewTab()
                                ->label('ID Proof'),
                        ]),
                ]),

            // System Access Section
            Infolists\Components\Section::make('System Access')
                ->schema([
                    Infolists\Components\TextEntry::make('user.email')
                        ->label('Login Email'),

                    Infolists\Components\TextEntry::make('user.roles.name')
                        ->badge()
                        ->label('Roles'),
                ])
                ->visible(fn ($record) => $record->user !== null)
                ->collapsed(),
        ]);
    }
}
