<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrganizationUnitResource\Pages;
use App\Filament\Admin\Resources\OrganizationUnitResource\RelationManagers;
use App\Models\OrganizationUnit;
use Filament\Forms;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrganizationUnitResource extends Resource
{
    protected static ?string $model = OrganizationUnit::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Organization Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Organization Unit')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Basic Information')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),
                                Forms\Components\Select::make('unit_type')
                                    ->options(OrganizationUnit::getUnitTypes())
                                    ->required(),
                                Forms\Components\Select::make('parent_id')
                                    ->label('Parent Unit')
                                    ->options(fn () => OrganizationUnit::query()
                                        ->where('id', '!=', $form->getRecord()?->id)
                                        ->pluck('name', 'id'))
                                    ->searchable(),
                                Forms\Components\Select::make('head_employee_id')
                                    ->relationship('headEmployee', 'first_name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Textarea::make('description')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Department Details')
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('location')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('annual_budget')
                                    ->numeric()
                                    ->prefix('$'),
                                Forms\Components\TextInput::make('current_headcount')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('max_headcount')
                                    ->numeric()
                                    ->default(0),
                            ]),

                        Forms\Components\Tabs\Tab::make('Organization')
                            ->schema([
                                Forms\Components\TextInput::make('level')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled(),
                                Forms\Components\TextInput::make('order_index')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\Toggle::make('is_active')
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Unit')
                    ->sortable(),
                Tables\Columns\TextColumn::make('headEmployee.full_name')
                    ->label('Head')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_headcount')
                    ->label('Headcount')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_type')
                    ->options(OrganizationUnit::getUnitTypes()),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent Unit')
                    ->options(fn () => OrganizationUnit::pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('level', 'asc');
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOrganizationStructure::route('/'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EmployeesRelationManager::class,
            RelationManagers\ChildrenRelationManager::class,
        ];
    }

    // Custom page for tree view
    public static function getTreeView(): View
    {
        $units = OrganizationUnit::with('children')
            ->rootUnits()
            ->orderBy('order_index')
            ->get();

        return view('admin.organization.tree', [
            'units' => $units,
        ]);
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
            'manage_hierarchy'
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('view_any_organization_unit');
    }

    public static function getNavigationGroup(): ?string
    {
        return auth()->user()->can('view_any_organization_unit') ? 'Organization Management' : null;
    }
}
