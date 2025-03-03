<?php

namespace App\Filament\Admin\Resources;


use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProfileResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'My Profile';
    protected static ?string $navigationGroup = 'Account AppSettings';
    protected static ?int $navigationSort = 1;
    protected static bool $shouldRegisterNavigation = true;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('id', auth()->user()->employee?->id);
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Profile')
                    ->tabs([
                        // Tab 1: Basic Information
                        Forms\Components\Tabs\Tab::make('Basic Information')
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
                                                    ->disabled()
                                                    ->dehydrated(),

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
                                                    ->displayFormat('d/m/Y')
                                                    ->disabled(),

                                                Forms\Components\Select::make('marital_status')
                                                    ->options([
                                                        'single' => 'Single',
                                                        'married' => 'Married',
                                                        'divorced' => 'Divorced',
                                                        'widowed' => 'Widowed',
                                                    ])
                                                    ->required(),
                                            ])
                                            ->columns(2)
                                            ->columnSpan(9)
                                    ]),
                            ]),

                        // Tab 2: Contact Information
                        Forms\Components\Tabs\Tab::make('Contact Information')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('phone_number')
                                            ->tel()
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
                                    ])
                                    ->columns(2),
                            ]),

                        // Tab 3: Employment Details
                        Forms\Components\Tabs\Tab::make('Employment Details')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('department.name')
                                            ->disabled(),

                                        Forms\Components\TextInput::make('jobTitle.name')
                                            ->label('Job Title')
                                            ->disabled(),

                                        Forms\Components\DatePicker::make('appointment_date')
                                            ->label('Date Joined')
                                            ->disabled()
                                            ->date('d/m/Y'),

                                        Forms\Components\TextInput::make('employment_status')
                                            ->disabled(),

                                        Forms\Components\TextInput::make('contract_type')
                                            ->disabled(),

                                        Forms\Components\DatePicker::make('contract_end_date')
                                            ->date('d/m/Y')
                                            ->disabled()
                                            ->visible(fn (Employee $record) =>
                                                $record->contract_type !== 'permanent'),
                                    ])
                                    ->columns(3),
                            ]),

                        // Tab 4: Skills & Qualifications
                        Forms\Components\Tabs\Tab::make('Skills & Qualifications')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Forms\Components\Placeholder::make('skills_note')
                                    ->content('Skills and qualifications can be managed in their respective sections below.')
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo')
                    ->circular()
                    ->defaultImageUrl(fn (Employee $record): string =>
                        "https://ui-avatars.com/api/?name=" . urlencode($record->full_name) . "&background=0D8ABC&color=fff&size=150&bold=true"
                    )
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->size(40),

                Tables\Columns\TextColumn::make('full_name')
                    ->description(fn($record) => $record->job_title)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->icon('heroicon-o-building-office')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->copyMessage('Phone number copied!'),

                Tables\Columns\TextColumn::make('email')
                    ->icon('heroicon-o-envelope')
                    ->copyable()
                    ->copyMessage('Email address copied!'),

                Tables\Columns\BadgeColumn::make('marital_status')
                    ->colors([
                        'primary' => 'single',
                        'success' => 'married',
                        'warning' => 'divorced',
                        'danger' => 'widowed',
                    ])
                    ->icon('heroicon-o-heart'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->button()
                    ->color('primary')
                    ->size('sm'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Complete your profile!')
            ->emptyStateDescription('Click the button below to create your profile')
            ->emptyStateIcon('heroicon-o-user-plus');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Admin\Resources\ProfileResource\RelationManagers\DependentsRelationManager::class,
            \App\Filament\Admin\Resources\ProfileResource\RelationManagers\EmergencyContactsRelationManager::class,
            \App\Filament\Admin\Resources\ProfileResource\RelationManagers\SkillsRelationManager::class,
            \App\Filament\Admin\Resources\ProfileResource\RelationManagers\DocumentsRelationManager::class,
            \App\Filament\Admin\Resources\ProfileResource\RelationManagers\EducationRelationManager::class,
            \App\Filament\Admin\Resources\ProfileResource\RelationManagers\FinancialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\ProfileResource\Pages\ListProfiles::route('/'),
            'create' => \App\Filament\Admin\Resources\ProfileResource\Pages\CreateProfile::route('/create'),
            'edit' => \App\Filament\Admin\Resources\ProfileResource\Pages\EditProfile::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }



    public static function canDeleteAny(): bool
    {
        return false; // Disable bulk deletion
    }

    public static function getNavigationBadge(): ?string
    {
        return null; // No badge needed for profile
    }
}
