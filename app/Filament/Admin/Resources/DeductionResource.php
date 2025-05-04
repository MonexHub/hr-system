<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DeductionResource\Pages;
use App\Filament\Admin\Resources\DeductionResource\RelationManagers;
use App\Models\Deduction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Support\Enums\IconPosition;

class DeductionResource extends Resource
{
    protected static ?string $model = Deduction::class;
    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';
    protected static ?string $navigationGroup = 'Payroll Management';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Employee Deduction';
    protected static ?string $pluralModelLabel = 'Employee Deductions';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        $universalCount = static::getModel()::where('applies_to_all', true)->count();
        return $universalCount > 0 ? (string) $universalCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canViewAny(): bool
    {
        // Allow access for roles that should see deductions
        return Auth::user()->hasAnyRole([
            'super_admin',
            'hr_manager',
            'financial_personnel',
            'department_head'
        ]);
    }

    public static function canCreate(): bool
    {
        // Only HR, finance, and super admin can create deductions
        return Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel']);
    }

    public static function canEdit($record): bool
    {
        // Only HR, finance, and super admin can edit deductions
        return Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel']);
    }

    public static function canDelete($record): bool
    {
        // Only super admin can delete deductions
        return Auth::user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Deduction Information')
                    ->description('Define the basic information for this deduction')
                    ->icon('heroicon-o-minus-circle')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('NSSF Contribution')
                                    ->helperText('Name of the deduction as shown to employees'),

                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(Deduction::class, 'code', ignoreRecord: true)
                                    ->placeholder('NSSF')
                                    ->rules(['required', 'max:50'])
                                    ->helperText('Unique identifier for this deduction'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->placeholder('Enter a description for this deduction')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('Deduction Configuration')
                    ->description('Set the value and application rules')
                    ->icon('heroicon-o-cog')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'fixed' => 'Fixed Amount',
                                        'percentage' => 'Percentage of Salary',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('value')
                                    ->required()
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix(fn (callable $get) => $get('type') === 'percentage' ? null : 'TSh')
                                    ->suffix(fn (callable $get) => $get('type') === 'percentage' ? '%' : null)
                                    ->placeholder(fn (callable $get) => $get('type') === 'percentage' ? '10.00' : '5000')
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('applies_to_all')
                                    ->label('Universal Deduction')
                                    ->helperText('Apply to all employees automatically')
                                    ->default(false)
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-tag')
                    ->copyable(),

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->badge()
                    ->color('danger'),

                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn (string $state): string =>
                    match($state) {
                        'fixed' => 'Fixed Amount',
                        'percentage' => 'Percentage',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'fixed',
                        'warning' => 'percentage',
                    ]),

                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->alignRight()
                    ->formatStateUsing(function (string $state, Deduction $record): string {
                        if ($record->type === 'percentage') {
                            return $state . '%';
                        } else {
                            return 'TSh ' . number_format($state, 2);
                        }
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('applies_to_all')
                    ->boolean()
                    ->label('Universal')
                    ->alignCenter()
                    ->tooltip(fn (Deduction $record): string =>
                    $record->applies_to_all ? 'Applied to all employees' : 'Applied selectively'
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'fixed' => 'Fixed Amount',
                        'percentage' => 'Percentage',
                    ])
                    ->indicator('Type'),

                Tables\Filters\TernaryFilter::make('applies_to_all')
                    ->label('Universal Deductions')
                    ->placeholder('All deductions')
                    ->trueLabel('Universal only')
                    ->falseLabel('Non-universal only'),

                Tables\Filters\Filter::make('value')
                    ->label('Value Range')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('value_from')
                                ->label('Minimum Value')
                                ->numeric()
                                ->step(0.01),
                            Forms\Components\TextInput::make('value_to')
                                ->label('Maximum Value')
                                ->numeric()
                                ->step(0.01),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value_from'],
                                fn (Builder $query, $value): Builder => $query->where('value', '>=', $value),
                            )
                            ->when(
                                $data['value_to'],
                                fn (Builder $query, $value): Builder => $query->where('value', '<=', $value),
                            );
                    })
                    ->indicator(function (array $data): ?string {
                        if (!$data['value_from'] && !$data['value_to']) {
                            return null;
                        }

                        if ($data['value_from'] && $data['value_to']) {
                            return 'Value: ' . $data['value_from'] . ' to ' . $data['value_to'];
                        }

                        return $data['value_from']
                            ? 'Value from: ' . $data['value_from']
                            : 'Value to: ' . $data['value_to'];
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->tooltip('Edit deduction')
                        ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])),
                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->visible(fn () => Auth::user()->hasRole('super_admin')),
                    Tables\Actions\Action::make('clone')
                        ->label('Clone')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->tooltip('Clone deduction')
                        ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel']))
                        ->action(function (Deduction $record) {
                            $clone = $record->replicate();
                            $clone->name = "Copy of {$record->name}";
                            $clone->code = "{$record->code}-COPY";
                            $clone->save();
                        }),
                ])
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->hasRole('super_admin')),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            // Export logic here
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('make_universal')
                        ->label('Make Universal')
                        ->icon('heroicon-o-globe-alt')
                        ->color('warning')
                        ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager']))
                        ->requiresConfirmation()
                        ->modalHeading('Make Deductions Universal')
                        ->modalDescription('Are you sure you want to make these deductions universal?')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $records->each(function ($record) {
                                $record->update(['applies_to_all' => true]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create New Deduction')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create First Deduction')
                    ->icon('heroicon-m-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeductions::route('/'),
            'create' => Pages\CreateDeduction::route('/create'),
            'edit' => Pages\EditDeduction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
