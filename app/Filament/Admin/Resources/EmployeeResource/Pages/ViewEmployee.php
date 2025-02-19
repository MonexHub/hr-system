<?php
namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Infolists;
use Filament\Infolists\Components\{Grid, Section, Split, TextEntry};
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Split::make([
                            Grid::make(1)
                                ->schema([
                                    Infolists\Components\ImageEntry::make('profile_photo')
                                        ->circular()
                                        ->size(100)
                                        ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&background=0D8ABC&color=fff'),
                                ])
                                ->columnSpan(1),

                            Grid::make(3)
                                ->schema([
                                    TextEntry::make('employee_code')
                                        ->label('Employee ID')
                                        ->color('primary')
                                        ->weight(FontWeight::Bold),

                                    TextEntry::make('full_name')
                                        ->label('Name')
                                        ->weight(FontWeight::Bold),

                                    TextEntry::make('jobTitle.name')
                                        ->label('Position'),

                                    TextEntry::make('department.name')
                                        ->label('Department')
                                        ->icon('heroicon-m-building-office-2'),

                                    TextEntry::make('employment_status')
                                        ->badge()
                                        ->color(fn(string $state): string => match (strtoupper($state)) {
                                            'ACTIVE' => 'success',
                                            'PROBATION' => 'warning',
                                            'SUSPENDED', 'TERMINATED' => 'danger',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn(string $state): string => strtoupper($state)),

                                    TextEntry::make('appointment_date')
                                        ->label('Joined Date')
                                        ->date('M d, Y'),
                                ])
                                ->columnSpan(2),
                        ])->from('md'),
                    ])
                    ->columnSpan('full'),

                // Rest of the sections collapsed by default
                $this->getPersonalInfoSection(),
                $this->getEmploymentDetailsSection(),
                $this->getContactInfoSection(),
                $this->getSystemAccessSection(),
            ])
            ->columns(3);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->color('gray'),
            $this->getTerminateAction(),
        ];
    }

    protected function getTerminateAction(): Actions\Action
    {
        return Actions\Action::make('terminate')
            ->icon('heroicon-m-no-symbol')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Terminate Employee')
            ->form([
                DatePicker::make('termination_date')
                    ->required()
                    ->default(now())
                    ->label('Last Working Day'),
                Textarea::make('termination_reason')
                    ->required()
                    ->rows(3),
            ])
            ->action(function (array $data) {
                $this->record->update([
                    'employment_status' => 'terminated',
                    'contract_end_date' => $data['termination_date'],
                    'termination_reason' => $data['termination_reason'],
                ]);
                $this->refreshFormData();
            })
            ->visible(fn ($record) => $record->employment_status === 'active');
    }

    protected function getPersonalInfoSection(): Section
    {
        return Section::make('Personal Information')
            ->schema([
                Grid::make(3)
                    ->schema([
                        TextEntry::make('gender')
                            ->icon('heroicon-m-user'),
                        TextEntry::make('birthdate')
                            ->date()
                            ->icon('heroicon-m-calendar'),
                        TextEntry::make('marital_status')
                            ->icon('heroicon-m-heart'),
                    ]),
            ])
            ->collapsed();
    }

    protected function getEmploymentDetailsSection(): Section
    {
        return Section::make('Employment Details')
            ->schema([
                Grid::make(3)
                    ->schema([
                        TextEntry::make('contract_type')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('net_salary')
                            ->money('TZS')
                            ->icon('heroicon-m-banknotes'),
                        TextEntry::make('reportingTo.full_name')
                            ->label('Reports To')
                            ->icon('heroicon-m-user'),
                        TextEntry::make('appointment_date')
                            ->label('Start Date')
                            ->date(),
                        TextEntry::make('contract_end_date')
                            ->label('End Date')
                            ->date()
                            ->visible(fn () => $this->record->contract_type !== 'permanent'),
                    ]),
            ])
            ->collapsed();
    }

    protected function getContactInfoSection(): Section
    {
        return Section::make('Contact Information')
            ->schema([
                Grid::make(3)
                    ->schema([
                        TextEntry::make('email')
                            ->copyable()
                            ->icon('heroicon-m-envelope'),
                        TextEntry::make('phone_number')
                            ->copyable()
                            ->icon('heroicon-m-phone'),
                        TextEntry::make('permanent_address')
                            ->icon('heroicon-m-home'),
                        TextEntry::make('city')
                            ->icon('heroicon-m-building-office'),
                        TextEntry::make('state'),
                        TextEntry::make('postal_code'),
                    ]),
            ])
            ->collapsed();
    }

    protected function getSystemAccessSection(): Section
    {
        return Section::make('System Access')
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextEntry::make('user.email')
                            ->label('Login Email')
                            ->copyable()
                            ->icon('heroicon-m-envelope'),
                        TextEntry::make('user.roles.name')
                            ->label('Roles')
                            ->badge()
                            ->color('gray'),
                    ]),
            ])
            ->collapsed()
            ->visible(fn () => $this->record->user !== null);
    }
}
