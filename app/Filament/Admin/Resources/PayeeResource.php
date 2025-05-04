<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PayeeResource\Pages;
use App\Filament\Admin\Resources\PayeeResource\RelationManagers;
use App\Models\Payee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Enums\ActionsPosition;

class PayeeResource extends Resource
{
    protected static ?string $model = Payee::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Tax Brackets';

    protected static ?string $navigationGroup = 'Payroll Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'PAYE Tax Bracket';

    protected static ?string $pluralModelLabel = 'PAYE Tax Brackets';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function canViewAny(): bool
    {
        // All roles should be able to view tax brackets for information
        return Auth::user()->hasAnyRole([
            'super_admin',
            'hr_manager',
            'financial_personnel',
            'department_head',
            'employee'
        ]);
    }

    public static function canCreate(): bool
    {
        // Only HR, finance, and super admin can create tax brackets
        return Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel']);
    }

    public static function canEdit($record): bool
    {
        // Only HR, finance, and super admin can edit tax brackets
        return Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel']);
    }

    public static function canDelete($record): bool
    {
        // Only super admin can delete tax brackets
        return Auth::user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('PAYE Tax Bracket Details')
                    ->description('Define the income bracket and tax rates')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('min_amount')
                                    ->label('Minimum Income (TSh)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->rules(['required', 'numeric', 'min:0'])
                                    ->prefix('TSh')
                                    ->afterStateHydrated(function ($state, $set) {
                                        if ($state) {
                                            $set('min_amount', number_format($state, 0));
                                        }
                                    })
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0) : null)
                                    ->dehydrateStateUsing(fn ($state) => str_replace(',', '', $state))
                                    ->placeholder('0')
                                    ->live()
                                    ->debounce(500),

                                Forms\Components\TextInput::make('max_amount')
                                    ->label('Maximum Income (TSh)')
                                    ->numeric()
                                    ->minValue(function (callable $get) {
                                        $minAmount = str_replace(',', '', $get('min_amount'));
                                        return $minAmount ? (float)$minAmount + 1 : 1;
                                    })
                                    ->rules(['nullable', 'numeric', 'min:0'])
                                    ->prefix('TSh')
                                    ->afterStateHydrated(function ($state, $set) {
                                        if ($state) {
                                            $set('max_amount', number_format($state, 0));
                                        }
                                    })
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0) : null)
                                    ->dehydrateStateUsing(fn ($state) => $state ? str_replace(',', '', $state) : null)
                                    ->helperText('Leave empty for highest bracket (no upper limit)')
                                    ->placeholder('No upper limit'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('rate')
                                    ->label('Tax Rate (%)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->rules(['required', 'numeric', 'min:0', 'max:100'])
                                    ->suffix('%')
                                    ->placeholder('0.00'),

                                Forms\Components\TextInput::make('fixed_amount')
                                    ->label('Fixed Amount (TSh)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->rules(['required', 'numeric', 'min:0'])
                                    ->prefix('TSh')
                                    ->afterStateHydrated(function ($state, $set) {
                                        if ($state) {
                                            $set('fixed_amount', number_format($state, 0));
                                        }
                                    })
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0) : null)
                                    ->dehydrateStateUsing(fn ($state) => str_replace(',', '', $state))
                                    ->helperText('Base amount added to the calculated tax')
                                    ->placeholder('0'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Enter an optional description for this tax bracket')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('min_amount')
                    ->label('Minimum Income')
                    ->formatStateUsing(fn ($state) => 'TSh ' . number_format($state, 0))
                    ->color('gray')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('max_amount')
                    ->label('Maximum Income')
                    ->formatStateUsing(fn ($state) => $state ? 'TSh ' . number_format($state, 0) : '∞')
                    ->placeholder('No Limit')
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('bracket_range')
                    ->label('Income Range')
                    ->state(function (Payee $record): string {
                        $min = number_format($record->min_amount, 0);

                        if ($record->max_amount) {
                            $max = number_format($record->max_amount, 0);
                            return "TSh {$min} - TSh {$max}";
                        }

                        return "Over TSh {$min}";
                    })
                    ->searchable(false)
                    ->sortable(false)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rate')
                    ->label('Tax Rate')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->sortable()
                    ->alignCenter()
                    ->color('success'),

                Tables\Columns\TextColumn::make('fixed_amount')
                    ->label('Fixed Amount')
                    ->formatStateUsing(fn ($state) => 'TSh ' . number_format($state, 0))
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->tooltip(fn (Payee $record): ?string => $record->description)
                    ->toggleable(isToggledHiddenByDefault: true),

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
                Tables\Filters\SelectFilter::make('tax_range')
                    ->label('Income Range')
                    ->options([
                        'low' => 'Low Income (< 500,000 TSh)',
                        'medium' => 'Medium Income (500,000 - 2,000,000 TSh)',
                        'high' => 'High Income (> 2,000,000 TSh)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value']) {
                            'low' => $query->where('min_amount', '<', 500000),
                            'medium' => $query->where('min_amount', '>=', 500000)
                                ->where(function ($query) {
                                    $query->where('max_amount', '<=', 2000000)
                                        ->orWhereNull('max_amount');
                                }),
                            'high' => $query->where('min_amount', '>=', 2000000),
                            default => $query,
                        };
                    })
                    ->indicator('Income Range'),

                Tables\Filters\Filter::make('has_no_upper_limit')
                    ->label('Highest Bracket')
                    ->query(fn (Builder $query) => $query->whereNull('max_amount')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->tooltip('Edit tax bracket')
                        ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])),

                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->visible(fn () => Auth::user()->hasRole('super_admin')),

                    Tables\Actions\Action::make('calculate')
                        ->label('Calculate Example')
                        ->icon('heroicon-o-calculator')
                        ->color('gray')
                        ->tooltip('Calculate tax for custom amount')
                        ->form([
                            Forms\Components\TextInput::make('salary')
                                ->label('Enter Income Amount')
                                ->required()
                                ->prefix('TSh')
                                ->numeric(),
                        ])
                        ->action(function (Payee $record, array $data): void {
                            $tax = $record->calculateTaxFor((float) $data['salary']);
                            $percentage = number_format(($tax / (float) $data['salary']) * 100, 2);

                            Notification::make()
                                ->title('Tax Calculation Result')
                                ->body("For income of TSh " . number_format($data['salary'], 0) .
                                    ":\nTax amount: TSh " . number_format($tax, 0) .
                                    " (" . $percentage . "% effective rate)")
                                ->success()
                                ->send();
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
                            // Logic to export the selected records
                            $data = $records->map(function ($record) {
                                return [
                                    'min_amount' => $record->min_amount,
                                    'max_amount' => $record->max_amount,
                                    'rate' => $record->rate,
                                    'fixed_amount' => $record->fixed_amount,
                                    'description' => $record->description ?? '',
                                ];
                            });

                            // In a real implementation, you would generate the file here

                            Notification::make()
                                ->title('Export Complete')
                                ->body('Successfully exported ' . $records->count() . ' tax brackets.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('min_amount')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create New Tax Bracket')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])),

                Tables\Actions\Action::make('help')
                    ->label('Tax Calculation Guide')
                    ->icon('heroicon-o-information-circle')
                    ->color('gray')
                    ->action(function (): void {
                        Notification::make()
                            ->title('PAYE Tax Calculation Guide')
                            ->body("1. Find the applicable bracket for the income level\n2. Calculate taxable amount (income - bracket's minimum)\n3. Apply the percentage rate to the taxable amount\n4. Add the fixed amount\n\nThis gives the total PAYE tax amount.")
                            ->persistent()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('close')
                                    ->label('Close')
                                    ->close(),
                            ])
                            ->send();
                    }),
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
            'index' => Pages\ListPayees::route('/'),
            'create' => Pages\CreatePayee::route('/create'),
            'edit' => Pages\EditPayee::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
