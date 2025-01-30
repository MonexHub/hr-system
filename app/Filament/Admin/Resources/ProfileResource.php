<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Employee\Resources\ProfileResource\Pages;
use App\Filament\Employee\Resources\ProfileResource\RelationManagers;
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
    protected static ?string $navigationGroup = 'Account';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('id', auth()->user()->employee?->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getProfilePhotoSection(),
                static::getProfileDetailsTabs(),
            ]);
    }

    protected static function getProfilePhotoSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Profile Photo')
            ->schema([
                Forms\Components\FileUpload::make('profile_photo')
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper()
                    ->directory('employee-photos')
                    ->imagePreviewHeight('150px')
                    ->alignCenter()
            ])
            ->collapsible()
            ->columns(1);
    }

    protected static function getProfileDetailsTabs(): Forms\Components\Tabs
    {
        return Forms\Components\Tabs::make('Profile Details')
            ->tabs([
                static::getPersonalInformationTab(),
                static::getContactDetailsTab(),
            ])
            ->columnSpanFull()
            ->persistTabInQueryString();
    }

    protected static function getPersonalInformationTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Personal Information')
            ->icon('heroicon-o-user')
            ->schema([
                static::getBasicInfoFields(),
                static::getPersonalDetailsFields(),
            ]);
    }

    protected static function getBasicInfoFields(): Forms\Components\Grid
    {
        return Forms\Components\Grid::make(3)
            ->schema([
                Forms\Components\TextInput::make('employee_code')
                    ->label('Employee ID')
                    ->disabled()
                    ->prefixIcon('heroicon-o-identification')
                    ->columnSpan(1),

                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->prefixIcon('heroicon-o-user')
                    ->columnSpan(1),

                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->prefixIcon('heroicon-o-user')
                    ->columnSpan(1),
            ]);
    }

    protected static function getPersonalDetailsFields(): Forms\Components\Grid
    {
        return Forms\Components\Grid::make(2)
            ->schema([
                Forms\Components\DatePicker::make('birthdate')
                    ->displayFormat('d M Y')
                    ->prefixIcon('heroicon-o-cake')
                    ->columnSpan(1),

                Forms\Components\Select::make('marital_status')
                    ->options([
                        'single' => 'Single',
                        'married' => 'Married',
                        'divorced' => 'Divorced',
                        'widowed' => 'Widowed',
                    ])
                    ->native(false)
                    ->prefixIcon('heroicon-o-heart')
                    ->columnSpan(1),
            ]);
    }

    protected static function getContactDetailsTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Contact Details')
            ->icon('heroicon-o-phone')
            ->schema([
                static::getContactFields(),
                static::getAddressFields(),
            ]);
    }

    protected static function getContactFields(): Forms\Components\Grid
    {
        return Forms\Components\Grid::make(2)
            ->schema([
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->required()
                    ->prefixIcon('heroicon-o-device-phone-mobile'),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->prefixIcon('heroicon-o-envelope'),
            ]);
    }

    protected static function getAddressFields(): Forms\Components\Fieldset
    {
        return Forms\Components\Fieldset::make('Address Information')
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('permanent_address')
                    ->columnSpanFull()
                    ->prefixIcon('heroicon-o-map-pin'),

                Forms\Components\TextInput::make('city')
                    ->required()
                    ->prefixIcon('heroicon-o-building-office'),

                Forms\Components\TextInput::make('state')
                    ->required()
                    ->prefixIcon('heroicon-o-flag'),

                Forms\Components\TextInput::make('postal_code')
                    ->prefixIcon('heroicon-o-tag'),
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
}
