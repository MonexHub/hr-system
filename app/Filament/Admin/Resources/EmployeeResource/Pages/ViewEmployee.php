<?php
namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Actions\ExportEmployeeProfileAction;
use App\Filament\Admin\Resources\EmployeeResource;
use App\Mail\NewEmployeeAccountSetupMail;
use App\Models\Employee;
use App\Services\BeemService;
use Dompdf\Image\Cache;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Infolists\Components\{Grid, Section, Split, TextEntry};
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            Actions\Action::make('viewResume')
                ->label('View Resume')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(fn ($record) => route('employee.resume', $record))
                ->openUrlInNewTab(),

            Actions\EditAction::make()
                ->color('gray'),

            Actions\Action::make('create_user_account')
                ->label('Create User Account')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Create User Account')
                ->modalDescription('Create a new user account for this employee and send setup instructions via email and SMS if available.')
                ->modalSubmitActionLabel('Create Account')
                ->visible(function (Employee $record) {
                    return auth()->user()->hasRole(['super_admin', 'hr_manager']) && !$record->user;
                })
                ->action(function (Employee $record) {
                    try {
                        // Create new user account
                        $user = \App\Models\User::create([
                            'name' => $record->full_name,
                            'email' => $record->email,
                            'password' => Hash::make(Str::random(16))
                        ]);

                        // Assign 'employee' role to the user
                        $user->assignRole('employee');

                        // Associate user with employee
                        $record->user_id = $user->id;
                        $record->save();

                        // Generate token for account setup
                        $token = Str::random(64);

                        // Store token in cache
                        Cache::put(
                            'account_setup_' . $record->id,
                            $token,
                            now()->addHours(48)
                        );

                        $setupUrl = route('employee.setup-account', [
                            'token' => $token,
                            'email' => $record->email,
                        ]);

                        $emailSent = false;
                        $smsSent = false;
                        $errors = [];

                        // Try to send email
                        try {
                            Mail::to($record->email)->send(
                                new NewEmployeeAccountSetupMail($record, $token)
                            );
                            $emailSent = true;
                        } catch (\Exception $e) {
                            $errors['email'] = $e->getMessage();
                            Log::error('Failed to send setup email', [
                                'employee_id' => $record->id,
                                'error' => $e->getMessage()
                            ]);
                        }

                        // Try to send SMS if phone number exists
                        if ($record->phone_number) {
                            try {
                                $beemService = new BeemService();
                                $smsMessage = "Welcome to " . config('app.name') . "! Set up your account at: " . $setupUrl;

                                $result = $beemService->sendSMS($record->phone_number, $smsMessage);

                                if ($result['success']) {
                                    $smsSent = true;
                                } else {
                                    $errors['sms'] = $result['error'] ?? 'Unknown SMS error';
                                    Log::warning('SMS sending failed for employee: ' . $record->id, [
                                        'error' => $result['error'] ?? 'Unknown error'
                                    ]);
                                }
                            } catch (\Exception $e) {
                                $errors['sms'] = $e->getMessage();
                                Log::error('Failed to send setup SMS', [
                                    'employee_id' => $record->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        // Determine notification type based on results
                        if ($emailSent || $smsSent) {
                            $channels = [];
                            if ($emailSent) $channels[] = 'email';
                            if ($smsSent) $channels[] = 'SMS';

                            $notification = Notification::make()
                                ->success()
                                ->title('Account Created')
                                ->body('User account created with "employee" role and setup instructions sent via ' . implode(' and ', $channels) . '.');

                            if (!empty($errors)) {
                                $failedChannels = array_keys($errors);
                                $notification->body($notification->getBody() . ' Failed to send via ' . implode(' and ', $failedChannels) . '.');
                            }

                            $notification->send();
                            $this->refreshFormData();
                            return true;
                        } else {
                            Notification::make()
                                ->warning()
                                ->title('Account Created With Warning')
                                ->body('User account was created with "employee" role but failed to send setup instructions through any channel. Please try resending the setup link.')
                                ->send();

                            Log::error('Account created but failed to send setup instructions through any channel', [
                                'employee_id' => $record->id,
                                'errors' => $errors
                            ]);
                            $this->refreshFormData();
                            return true;
                        }

                    } catch (\Exception $e) {
                        Log::error('Failed to create user account', [
                            'employee_id' => $record->id,
                            'error' => $e->getMessage()
                        ]);

                        Notification::make()
                            ->danger()
                            ->title('Error')
                            ->body('Failed to create user account: ' . $e->getMessage())
                            ->send();

                        throw $e;
                    }
                }),

            Actions\Action::make('resend_setup')
                ->label('Resend Setup Link')
                ->icon('heroicon-o-envelope')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Resend Account Setup Link')
                ->modalDescription('This will generate a new setup link and send it to the employee via email and SMS if available.')
                ->modalSubmitActionLabel('Resend Link')
                ->visible(function (Employee $record) {
                    return auth()->user()->hasRole(['super_admin', 'hr_manager']) && $record->user !== null;
                })
                ->action(function (Employee $record) {
                    // Generate new token
                    $token = Str::random(64);

                    // Store token in cache
                    Cache::put(
                        'account_setup_' . $record->id,
                        $token,
                        now()->addHours(48)
                    );

                    $setupUrl = route('employee.setup-account', [
                        'token' => $token,
                        'email' => $record->email,
                    ]);

                    $emailSent = false;
                    $smsSent = false;
                    $errors = [];

                    // Try to send email
                    try {
                        Mail::to($record->email)->send(
                            new NewEmployeeAccountSetupMail($record, $token)
                        );
                        $emailSent = true;
                    } catch (\Exception $e) {
                        $errors['email'] = $e->getMessage();
                        Log::error('Failed to send setup email', [
                            'employee_id' => $record->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    // Try to send SMS if phone number exists
                    if ($record->phone_number) {
                        try {
                            $beemService = new BeemService();
                            $smsMessage = "Your " . config('app.name') . " account setup link has been reset. Set up your account at: " . $setupUrl;

                            $result = $beemService->sendSMS($record->phone_number, $smsMessage);

                            if ($result['success']) {
                                $smsSent = true;
                            } else {
                                $errors['sms'] = $result['error'] ?? 'Unknown SMS error';
                                Log::warning('SMS sending failed for employee: ' . $record->id, [
                                    'error' => $result['error'] ?? 'Unknown error'
                                ]);
                            }
                        } catch (\Exception $e) {
                            $errors['sms'] = $e->getMessage();
                            Log::error('Failed to send setup SMS', [
                                'employee_id' => $record->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    // Determine notification type based on results
                    if ($emailSent || $smsSent) {
                        $channels = [];
                        if ($emailSent) $channels[] = 'email';
                        if ($smsSent) $channels[] = 'SMS';

                        $notification = Notification::make()
                            ->success()
                            ->title('Setup Link Sent')
                            ->body('Setup link sent via ' . implode(' and ', $channels) . '.');

                        if (!empty($errors)) {
                            $failedChannels = array_keys($errors);
                            $notification->body($notification->getBody() . ' Failed to send via ' . implode(' and ', $failedChannels) . '.');
                        }

                        $notification->send();
                        $this->refreshFormData();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Setup Link Failed')
                            ->body('Failed to send setup link through any channel. Please try again.')
                            ->send();

                        Log::error('Failed to send setup link through any channel', [
                            'employee_id' => $record->id,
                            'errors' => $errors
                        ]);
                    }
                }),

            Actions\Action::make('exportProfile')
                ->label('Export Profile')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->visible(fn() => auth()->user()->can('export_employee'))
                ->action(function (Employee $record) {
                    return app(ExportEmployeeProfileAction::class)->execute($record);
                }),

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
