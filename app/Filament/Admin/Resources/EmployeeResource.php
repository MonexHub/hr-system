<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Actions\ExportEmployeeProfileAction;
use App\Filament\Admin\Resources\EmployeeResource\Pages;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\EducationsRelationManager;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\ExperienceRelationManager;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\LeaveRequestsRelationManager;
use App\Filament\Imports\EmployeeImporter;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Employee Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Employee Details')
                ->tabs([
                    // Personal Information Tab
                    Forms\Components\Tabs\Tab::make('Personal Information')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\FileUpload::make('profile_photo')
                                ->image()
                                ->imageEditor()
                                ->circleCropper()
                                ->directory('employee-photos')
                                ->columnSpanFull(),

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
                                ]),
                        ])
                        ->columns(2),

                    // Employment Details Tab
                    Forms\Components\Tabs\Tab::make('Employment Details')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            Forms\Components\Select::make('department_id')
                                ->relationship('department', 'name')
                                ->required()
                                ->searchable(),

                            Forms\Components\Select::make('reporting_to')
                                ->relationship('reportingTo', 'first_name', function ($query) {
                                    return $query->whereNotNull('appointment_date');
                                })
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                                ->searchable(),

                            Forms\Components\TextInput::make('job_title')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\Select::make('employment_status')
                                ->options([
                                    'active' => 'Active',
                                    'probation' => 'Probation',
                                    'suspended' => 'Suspended',
                                    'terminated' => 'Terminated',
                                    'resigned' => 'Resigned',
                                ])
                                ->required(),

                            Forms\Components\Select::make('contract_type')
                                ->options([
                                    'permanent' => 'Permanent',
                                    'contract' => 'Contract',
                                    'probation' => 'Probation',
                                    'intern' => 'Intern',
                                ])
                                ->required(),

                            Forms\Components\DatePicker::make('appointment_date')
                                ->required()
                                ->displayFormat('d/m/Y'),

                            Forms\Components\DatePicker::make('contract_end_date')
                                ->displayFormat('d/m/Y')
                                ->visible(fn (callable $get) => $get('contract_type') !== 'permanent'),

                            Forms\Components\TextInput::make('salary')
                                ->numeric()
                                ->prefix('TSh')
                                ->required(),
                        ])
                        ->columns(2),

                    // Contact Information Tab
                    Forms\Components\Tabs\Tab::make('Contact Information')
                        ->icon('heroicon-o-phone')
                        ->schema([
                            Forms\Components\TextInput::make('phone_number')
                                ->tel()
                                ->required(),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->afterStateHydrated(function ($component, $state, ?Employee $record) {
                                    if (!$state && $record) {
                                        $component->state($record->user?->email);
                                    }
                                }),
                            Forms\Components\TextInput::make('permanent_address')
                                ->required(),

                            Forms\Components\TextInput::make('city')
                                ->required(),

                            Forms\Components\TextInput::make('state')
                                ->required(),

                            Forms\Components\TextInput::make('postal_code'),

                            Forms\Components\TextInput::make('emergency_contact_name')
                              ,

                            Forms\Components\TextInput::make('emergency_contact_phone')
                                ->tel(),
                        ])
                        ->columns(2),

                    // Documents Tab
                    Forms\Components\Tabs\Tab::make('Documents')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\FileUpload::make('cv')
                                ->label('CV/Resume')
                                ->acceptedFileTypes(['application/pdf'])
                                ->directory('employee-documents/cv'),

                            Forms\Components\FileUpload::make('id_proof')
                                ->label('ID Proof')
                                ->image()
                                ->directory('employee-documents/id'),

                            Forms\Components\TextInput::make('nssf_number')
                                ->label('NSSF Number'),

                            Forms\Components\TextInput::make('bank_account')
                                ->label('Bank Account Number'),

                            Forms\Components\TextInput::make('bank_name')
                                ->label('Bank Name'),
                        ])
                        ->columns(2),

                    // System Access Tab
                    Forms\Components\Tabs\Tab::make('System Access')
                        ->icon('heroicon-o-key')
                        ->schema([
                            Forms\Components\Toggle::make('create_user_account')
                                ->label('Create User Account?')
                                ->default(true)
                                ->reactive(),

                            Forms\Components\TextInput::make('password')
                                ->password()
                                ->dehydrated(fn ($state) => filled($state))
                                ->required(fn (callable $get) => $get('create_user_account'))
                                ->visible(fn (callable $get) => $get('create_user_account')),

                            Forms\Components\Select::make('roles')
                                ->multiple()
                                ->options(function () {
                                    return Role::all()->pluck('name', 'name');
                                })
                                ->preload()
                                ->visible(fn (callable $get) => $get('create_user_account')),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),
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
                    ->description(fn (Employee $record): string => $record->job_title),

                Tables\Columns\TextColumn::make('department.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('organizationUnit.name')
                ->label('Unit')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('employment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'probation' => 'warning',
                        'suspended' => 'danger',
                        'terminated' => 'danger',
                        'resigned' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('appointment_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name'),

                Tables\Filters\SelectFilter::make('employment_status')
                    ->options([
                        'active' => 'Active',
                        'probation' => 'Probation',
                        'suspended' => 'Suspended',
                        'terminated' => 'Terminated',
                        'resigned' => 'Resigned',
                    ]),

                Tables\Filters\SelectFilter::make('contract_type')
                    ->options([
                        'permanent' => 'Permanent',
                        'contract' => 'Contract',
                        'probation' => 'Probation',
                        'intern' => 'Intern',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                ExportEmployeeProfileAction::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(EmployeeImporter::class)->color('warning')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportEmployeeProfileAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
           LeaveRequestsRelationManager::class,
            EducationsRelationManager::class,
           ExperienceRelationManager::class,
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('employment_status', 'active')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
