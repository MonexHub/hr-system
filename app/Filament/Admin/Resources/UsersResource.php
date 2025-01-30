<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UsersResource\Pages;
use App\Filament\Admin\Resources\UsersResource\RelationManagers;
use App\Models\User;
use App\Models\Users;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UsersResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'System Management';
    protected static ?int $navigationSort = 1;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'terminate',
            'manage_roles'
        ];
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->confirmed()
                            ->maxLength(255)
                            ->visible(fn(string $context): bool => $context === 'create'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->maxLength(255)
                            ->required(fn(string $context): bool => $context === 'create')
                            ->visible(fn(string $context): bool => $context === 'create'),

                        Forms\Components\Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable(),
                    ])->columns(2),

                Forms\Components\Section::make('Employee Details')
                    ->schema([
                        Forms\Components\Select::make('employee.department_id')
                            ->relationship('employee.department', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('employee.organization_unit_id')
                            ->relationship('employee.organizationUnit', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('employee.employment_status')
                            ->options([
                                'active' => 'Active',
                                'probation' => 'Probation',
                                'suspended' => 'Suspended',
                                'terminated' => 'Terminated',
                                'resigned' => 'Resigned',
                            ])
                            ->required()
                            ->default('active'),

                        Forms\Components\Select::make('employee.contract_type')
                            ->options([
                                'permanent' => 'Permanent',
                                'contract' => 'Contract',
                                'probation' => 'Probation',
                                'intern' => 'Intern',
                            ])
                            ->required()
                            ->default('permanent'),

                        Forms\Components\DatePicker::make('employee.appointment_date')
                            ->required()
                            ->default(now()),

                        Forms\Components\DatePicker::make('employee.contract_end_date')
                            ->visible(fn(callable $get) => $get('employee.contract_type') !== 'permanent'),

                        Forms\Components\DatePicker::make('employee.termination_date')
                            ->visible(fn(callable $get) => $get('employee.employment_status') === 'terminated'),

                        Forms\Components\Textarea::make('employee.termination_reason')
                            ->visible(fn(callable $get) => $get('employee.employment_status') === 'terminated')
                            ->columnSpanFull(),
                    ])->columns(2)
                    ->hidden(fn(?User $record) => $record === null || !$record->employee()->exists()),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('employee.profile_photo')
                    ->circular()
                    ->defaultImageUrl(function ($record): string {
                        return "https://ui-avatars.com/api/?name=" . urlencode($record->name) . "&color=FFFFFF&background=111827";
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('employee.organizationUnit.name')
                    ->label('Unit')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.employment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'probation' => 'warning',
                        'suspended', 'terminated' => 'danger',
                        default => 'gray',
                    }),
            ]);
            // ... rest of the table configuration ...
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['employee.department', 'employee.unit', 'roles'])
            ->latest();
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUsers::route('/create'),
            'edit' => Pages\EditUsers::route('/{record}/edit'),
        ];
    }
}
