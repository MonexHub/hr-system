<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Actions\ExportEmployeeProfileAction;
use App\Filament\Admin\Resources\EmployeeResource\Pages;
use App\Filament\Admin\Resources\ProfileResource\RelationManagers\DependentsRelationManager;
use App\Filament\Admin\Resources\ProfileResource\RelationManagers\DocumentsRelationManager;
use App\Filament\Admin\Resources\ProfileResource\RelationManagers\EducationRelationManager;
use App\Filament\Admin\Resources\ProfileResource\RelationManagers\EmergencyContactsRelationManager;
use App\Filament\Admin\Resources\ProfileResource\RelationManagers\FinancialsRelationManager;
use App\Filament\Admin\Resources\ProfileResource\RelationManagers\SkillsRelationManager;
use App\Filament\Imports\EmployeeImporter;
use App\Mail\NewEmployeeAccountSetupMail;
use App\Models\Employee;
use App\Models\EmployeeImport;
use App\Services\BeemService;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EmployeeResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Employee::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Employee Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    // Step 1: Personal Information
                    Forms\Components\Wizard\Step::make('Personal Information')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\Grid::make()
                                ->columns(12)
                                ->schema([
                                    Forms\Components\Section::make()
                                        ->schema([
                                            Forms\Components\FileUpload::make('profile_photo')
                                                ->image()
                                                ->imageEditor()
                                                ->circleCropper()
                                                ->directory('employee-photos')
                                        ])
                                        ->columnSpan(3),

                                    Forms\Components\Section::make()
                                        ->schema([
                                            Forms\Components\TextInput::make('employee_code')
                                                ->default('EMP-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT))
                                                ->disabled()
                                                ->dehydrated()
                                                ->required(),

                                            Forms\Components\TextInput::make('first_name')
                                                ->required()
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('middle_name')
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('last_name')
                                                ->required()
                                                ->maxLength(255),

                                            Forms\Components\Select::make('gender')
                                                ->options([
                                                    'male' => 'Male',
                                                    'female' => 'Female',
                                                    'other' => 'Other',
                                                ])
                                                ->searchable()
                                                ->preload(true)
                                                ->required(),

                                            Forms\Components\TextInput::make('branch')
                                                ->dehydrated()
                                                ->required(),

                                            Forms\Components\DatePicker::make('birthdate')
                                                ->required()
                                                ->maxDate(now()->subYears(18))
                                                ->displayFormat('d/m/Y'),

                                            Forms\Components\Select::make('marital_status')
                                                ->options([
                                                    'single' => 'Single',
                                                    'married' => 'Married',
                                                    'divorced' => 'Divorced',
                                                    'widowed' => 'Widowed',
                                                ])
                                                ->searchable()
                                                ->preload(true)
                                        ])
                                        ->columns(2)
                                        ->columnSpan(9)
                                ])
                        ])->columnSpanFull(12),

                    // Step 2: Employment Details
                    Forms\Components\Wizard\Step::make('Employment Details')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Select::make('department_id')
                                        ->relationship('department', 'name')
                                        ->required()
                                        ->searchable()->preload(true)
                                        ->visible(fn() => auth()->user()->can('view_any_department'))
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state) {
                                                // Get the department
                                                $department = \App\Models\Department::find($state);
                                                if ($department && $department->manager) {
                                                    // Find the employee record for the department manager
                                                    $managerEmployee = \App\Models\Employee::where('user_id', $department->manager_id)->first();
                                                    if ($managerEmployee) {
                                                        $set('reporting_to', $managerEmployee->id);
                                                    }
                                                }
                                            }
                                        }),

                                    Forms\Components\Select::make('reporting_to')
                                        ->relationship('reportingTo', 'first_name', function ($query) {
                                            return $query->whereNotNull('appointment_date');
                                        })
                                        ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                                        ->searchable()->preload(true)
                                        ->visible(fn(callable $get) => !empty($get('department_id'))),
                                    Forms\Components\Select::make('job_title_id')
                                        ->label('Job Title')
                                        ->options(function (callable $get) {
                                            $departmentId = $get('department_id');
                                            if ($departmentId) {
                                                return \App\Models\JobTitle::where('department_id', $departmentId)
                                                    ->pluck('name', 'id')
                                                    ->toArray(); // Convert to array explicitly
                                            }
                                            return [];
                                        })
                                        ->required()
                                        ->searchable()
                                        ->preload(true)
                                        ->live()
                                        ->visible(fn(callable $get) => filled($get('department_id')))
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if (filled($state)) { // Using filled() instead of direct check
                                                $jobTitle = \App\Models\JobTitle::find($state);
                                                if ($jobTitle) {
                                                    $set('net_salary', $jobTitle->net_salary_min);
                                                }
                                            }
                                        }),

                                    Forms\Components\TextInput::make('net_salary')
                                        ->label('Net Salary')
                                        ->numeric()
                                        ->prefix('TZS')
                                        ->required()
                                        ->minValue(function (callable $get) {
                                            $jobTitleId = $get('job_title_id');
                                            if ($jobTitleId) {
                                                $jobTitle = \App\Models\JobTitle::find($jobTitleId);
                                                return $jobTitle?->net_salary_min ?? 0;
                                            }
                                            return 0;
                                        })
                                        ->maxValue(function (callable $get) {
                                            $jobTitleId = $get('job_title_id');
                                            if ($jobTitleId) {
                                                $jobTitle = \App\Models\JobTitle::find($jobTitleId);
                                                return $jobTitle?->net_salary_max ?? PHP_FLOAT_MAX;
                                            }
                                            return PHP_FLOAT_MAX;
                                        })
                                        ->hint(function (callable $get) {
                                            $jobTitleId = $get('job_title_id');
                                            if ($jobTitleId) {
                                                $jobTitle = \App\Models\JobTitle::find($jobTitleId);
                                                if ($jobTitle) {
                                                    return "Salary range: " . $jobTitle->net_salary_range;
                                                }
                                            }
                                            return null;
                                        })
                                        ->visible(fn() => auth()->user()->hasRole(['super_admin', 'hr_manager'])),

                                    Forms\Components\Select::make('employment_status')
                                        ->options([
                                            'active' => 'Active',
                                            'probation' => 'Probation',
                                            'suspended' => 'Suspended',
                                            'terminated' => 'Terminated',
                                            'resigned' => 'Resigned',
                                        ])
                                        ->required()
                                        ->searchable()
                                        ->preload(true)
                                        ->visible(fn() => auth()->user()->hasRole(['super_admin', 'hr_manager'])),

                                    Forms\Components\Select::make('contract_type')
                                        ->options([
                                            'permanent' => 'Permanent',
                                            'contract' => 'Contract',
                                            'probation' => 'Probation',
                                            'intern' => 'Intern',
                                        ])
                                        ->required()
                                        ->preload(true)
                                        ->searchable()
                                        ->reactive()
                                        ->visible(fn() => auth()->user()->hasRole(['super_admin', 'hr_manager'])),

                                    Forms\Components\DatePicker::make('appointment_date')
                                        ->required()
                                        ->displayFormat('d/m/Y')
                                        ->visible(fn() => auth()->user()->hasRole(['super_admin', 'hr_manager'])),

                                    Forms\Components\DatePicker::make('contract_end_date')
                                        ->displayFormat('d/m/Y')
                                        ->visible(fn(callable $get) =>
                                            $get('contract_type') !== 'permanent' &&
                                            auth()->user()->hasRole(['super_admin', 'hr_manager'])
                                        ),
                                ])
                                ->columns(2)
                        ]),

                    // Step 3: Contact Information
                    Forms\Components\Wizard\Step::make('Contact Information')
                        ->icon('heroicon-o-phone')
                        ->schema([
                            Forms\Components\Grid::make()
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('phone_number')
                                        ->tel()
                                        ->prefix('255')
                                        ->required(),

                                    Forms\Components\TextInput::make('email')
                                        ->email()
                                        ->required()
                                        ->unique(ignoreRecord: true),

                                    Forms\Components\TextInput::make('permanent_address')
                                        ->required(),

                                    Forms\Components\TextInput::make('city')
                                        ->required(),
                                    Forms\Components\TextInput::make('postal_code'),

                                    Forms\Components\TextInput::make('emergency_contact_name'),

                                    Forms\Components\TextInput::make('emergency_contact_phone')
                                        ->tel(),
                                ])
                        ]),

                    // Step 4: System Access
                    Forms\Components\Wizard\Step::make('System Access')
                        ->icon('heroicon-o-key')
                        ->schema([
                            Forms\Components\Grid::make()
                                ->columns(2)
                                ->schema([
                                    Forms\Components\Section::make()
                                        ->schema([
                                            Forms\Components\Select::make('roles')
                                                ->multiple()
                                                ->label('Assign Roles')
                                                ->options(function () {
                                                    if (auth()->user()->hasRole('super_admin')) {
                                                        return Role::all()->pluck('name', 'name');
                                                    }
                                                    return Role::whereNotIn('name', ['super_admin'])->pluck('name', 'name');
                                                })
                                                ->required()
                                                ->dehydrated(fn ($operation) => $operation === 'edit')
                                                ->preload(true)
                                                ->visible(fn() => auth()->user()->hasRole(['super_admin', 'hr_manager']))
                                        ])
                                        ->columns(1)
                                ])
                        ])
                ])
                    ->skippable()
                ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo')
                    ->circular()
                    ->defaultImageUrl(function (Employee $record): string {
                        $name = urlencode($record->full_name);
                        return "https://ui-avatars.com/api/?name={$name}&background=0D8ABC&color=fff&size=150&bold=true";
                    })
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->size(40),

                Tables\Columns\TextColumn::make('employee_code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name'])
                    ->description(fn(Employee $record): string =>
                        $record->jobTitle?->name ?? 'No Position'
                    ),
                Tables\Columns\TextColumn::make('department.name')
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => auth()->user()->can('view_any_department')),
                Tables\Columns\TextColumn::make('branch')
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => auth()->user()->can('view_any_department')),


                Tables\Columns\TextColumn::make('employment_status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst(strtolower($state)))
                    ->color(fn(string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'PROBATION' => 'warning',
                        'SUSPENDED', 'TERMINATED' => 'danger',
                        default => 'gray',
                    })
                    ->visible(fn() => auth()->user()->hasRole(['super_admin', 'hr_manager'])),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('appointment_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn() => auth()->user()->hasRole(['super_admin', 'hr_manager'])),
                Tables\Columns\IconColumn::make('account_setup')
                    ->label('Account Setup')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->user?->password !== null)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->alignCenter()
                    ->visible(fn() => auth()->user()->hasRole(['super_admin', 'hr_manager'])),
                Tables\Columns\TextColumn::make('contract_status')
                    ->label('Contract Status')
                    ->badge()
                    ->formatStateUsing(function (Employee $record): string {
                        if ($record->contract_type === 'permanent') {
                            return 'Permanent';
                        }

                        if ($record->contract_type === 'probation') {
                            $daysLeft = $record->daysUntilProbationEnds();
                            if ($daysLeft < 0) {
                                return 'Probation Ended';
                            }
                            return "Probation ({$daysLeft} days left)";
                        }

                        if ($record->contract_type === 'contract') {
                            $daysLeft = $record->daysUntilContractExpires();
                            return "Contract ({$daysLeft} days left)";
                        }

                        return $record->contract_type;
                    })
                    ->color(function (Employee $record): string {
                        if ($record->contract_type === 'permanent') {
                            return 'success';
                        }

                        if ($record->contract_type === 'probation') {
                            $daysLeft = $record->daysUntilProbationEnds();
                            if ($daysLeft < 0) return 'danger';
                            if ($daysLeft <= 7) return 'warning';
                            return 'info';
                        }

                        if ($record->contract_type === 'contract') {
                            $daysLeft = $record->daysUntilContractExpires();
                            if ($daysLeft < 0) return 'danger';
                            if ($daysLeft <= 30) return 'warning';
                            return 'success';
                        }

                        return 'gray';
                    })

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name')
                    ->visible(fn() => auth()->user()->can('view_any_department')),
                Tables\Filters\SelectFilter::make('employment_status')
                    ->options([
                        'active' => 'Active',
                        'probation' => 'Probation',
                        'suspended' => 'Suspended',
                        'terminated' => 'Terminated',
                        'resigned' => 'Resigned',
                    ])
                    ->visible(fn() => auth()->user()->hasRole(['super_admin', 'hr_manager'])),
                Tables\Filters\SelectFilter::make('contract_status')
                    ->options([
                        'probation_ending' => 'Probation Ending Soon',
                        'probation_ended' => 'Probation Ended',
                        'contract_expiring' => 'Contract Expiring Soon',
                        'contract_expired' => 'Contract Expired',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'probation_ending' => $query->probationEnding(7),
                            'probation_ended' => $query->where('contract_type', 'probation')
                                ->whereRaw('DATEDIFF(NOW(), appointment_date) > ?', [Employee::PROBATION_DURATION * 30]),
                            'contract_expiring' => $query->where('contract_type', 'contract')
                                ->contractExpiringSoon(30),
                            'contract_expired' => $query->where('contract_type', 'contract')
                                ->whereNotNull('contract_end_date')
                                ->whereDate('contract_end_date', '<', now()),
                            default => $query
                        };
                    })
                    ->visible(fn() => auth()->user()->hasRole(['super_admin', 'hr_manager'])),

                Tables\Filters\SelectFilter::make('contract_type')
                    ->options([
                        'permanent' => 'Permanent',
                        'contract' => 'Contract',
                        'probation' => 'Probation',
                        'intern' => 'Intern',
                    ])
                    ->visible(fn() => auth()->user()->hasRole(['super_admin', 'hr_manager']))
                ,
            ])
            ->actions([
                Tables\Actions\Action::make('create_user_account')
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
                                'password' => Hash::make(Str::random(16)) // temporary password
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

                Tables\Actions\ViewAction::make()
                    ->visible(fn(Employee $record) => auth()->user()->can('view', $record)),

                Tables\Actions\EditAction::make()
                    ->visible(fn(Employee $record) => auth()->user()->can('update', $record)),
                Tables\Actions\Action::make('resend_setup')
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
                ExportEmployeeProfileAction::make()
                    ->visible(fn() => auth()->user()->can('export_employee')),
            ])
            ->headerActions([
//                ImportAction::make()
//                ->importer(EmployeeImporter::class)
//                ->visible(fn() => auth()->user()->can('import_employee')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->can('delete_any_employee')),
//                    ExportEmployeeProfileAction::make()
//                        ->visible(fn() => auth()->user()->can('export_employee')),
                ]),
            ]);
    }



    public static function getRelations(): array
    {
        return [
       DependentsRelationManager::class,
           EmergencyContactsRelationManager::class,
            SkillsRelationManager::class,
           DocumentsRelationManager::class,
           EducationRelationManager::class,
           FinancialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'export',
            'import',
            'manage_roles'
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Department managers can only see employees in their department
        if (auth()->user()->hasRole('department_manager')) {
            $query->where('department_id', auth()->user()->employee?->department_id);
        } // HR managers can see all employees
        else if (auth()->user()->hasRole(['hr_manager', 'super_admin'])) {
            return $query;
        } // Regular employees can only see their own record
        else if (auth()->user()->hasRole('employee')) {
            $query->where('id', auth()->user()->employee?->id);
        } // For users with no specific role
        else {
            $query->where('id', null); // No access
        }

        return $query->whereNull('deleted_at');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('employment_status', 'active')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('view_any_employee');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_any_employee');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_employee');
    }

    public static function afterCreate(Model $record): void
    {
        // Generate a random token for account setup
        $token = Str::random(64);

        // Store the token in cache with expiry (e.g., 48 hours)
        Cache::put('account_setup_' . $record->id, $token, now()->addHours(48));

        // Send email to employee
        Mail::to($record->email)->send(new NewEmployeeAccountSetupMail($record, $token));
    }

    public static function getImports(): array
    {
        return [
            EmployeeImporter::class, // Ensure this is included!
        ];
    }


}
