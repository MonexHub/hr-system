<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LoanTypeResource\Pages;
use App\Filament\Resources\LoanTypeResource\RelationManagers\EmployeeLoansRelationManager;
use App\Models\LoanType;
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

class LoanTypeResource extends Resource
{
    protected static ?string $model = LoanType::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Payroll Management';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Loan Type';

    protected static ?string $pluralModelLabel = 'Loan Types';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function canViewAny(): bool
    {
        // Allow access for roles that should see loan information
        return Auth::user()->hasAnyRole([
            'super_admin',
            'hr_manager',
            'financial_personnel',
            'department_head'
        ]);
    }

    public static function canCreate(): bool
    {
        // Only HR, finance, and super admin can create loan types
        return Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel']);
    }

    public static function canEdit($record): bool
    {
        // Only HR, finance, and super admin can edit loan types
        return Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel']);
    }

    public static function canDelete($record): bool
    {
        // Only super admin can delete loan types
        return Auth::user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Loan Type Details')
                    ->description('Define the basic information for this loan type')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Employee Advance')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('EMP-ADV')
                            ->unique(ignorable: fn ($record) => $record)
                            ->rules(['required', 'max:50']),
                    ])->columns(3),

                Forms\Components\Section::make('Financial Parameters')
                    ->description('Set the financial parameters for this loan type')
                    ->icon('heroicon-o-currency-dollar')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('minimum_salary_required')
                            ->required()
                            ->numeric()
                            ->prefix('TSh')
                            ->label('Minimum Salary')
                            ->helperText('Minimum salary required to qualify')
                            ->minValue(0)
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0) : null)
                            ->dehydrateStateUsing(fn ($state) => str_replace(',', '', $state))
                            ->placeholder('0'),

                        Forms\Components\TextInput::make('max_amount_cap')
                            ->required()
                            ->numeric()
                            ->prefix('TSh')
                            ->label('Maximum Amount')
                            ->helperText('Maximum loan amount that can be issued')
                            ->minValue(0)
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0) : null)
                            ->dehydrateStateUsing(fn ($state) => str_replace(',', '', $state))
                            ->placeholder('0'),

                        Forms\Components\TextInput::make('repayment_months')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(60)
                            ->suffix('months')
                            ->helperText('Duration of loan repayment period (1-60 months)'),
                    ])->columns(3),

                Forms\Components\Section::make('Additional Information')
                    ->description('Provide detailed information about this loan type')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-o-tag')
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('minimum_salary_required')
                    ->money('TSH')
                    ->label('Min. Salary')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('max_amount_cap')
                    ->money('TSH')
                    ->label('Max. Amount')
                    ->sortable()
                    ->alignRight()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('eligible_salary_range')
                    ->label('Salary Range')
                    ->state(function (LoanType $record): string {
                        $min = number_format($record->minimum_salary_required, 0);
                        return "TSh {$min}+";
                    })
                    ->tooltip('Minimum eligible salary')
                    ->searchable(false)
                    ->sortable(false)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('repayment_months')
                    ->suffix(' months')
                    ->sortable()
                    ->icon('heroicon-m-clock')
                    ->iconPosition(IconPosition::Before)
                    ->color('warning'),

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
                Tables\Filters\SelectFilter::make('repayment_months')
                    ->options([
                        3 => '3 months',
                        6 => '6 months',
                        12 => '12 months',
                        24 => '24 months',
                        36 => '36 months',
                        48 => '48 months',
                        60 => '60 months',
                    ])
                    ->indicator('Repayment Period'),

                Tables\Filters\Filter::make('minimum_salary_required')
                    ->form([
                        Forms\Components\TextInput::make('minimum_salary_required_from')
                            ->label('Minimum Salary From')
                            ->numeric()
                            ->prefix('TSh'),
                        Forms\Components\TextInput::make('minimum_salary_required_to')
                            ->label('Minimum Salary To')
                            ->numeric()
                            ->prefix('TSh'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['minimum_salary_required_from'],
                                fn (Builder $query, $amount): Builder => $query->where('minimum_salary_required', '>=', $amount),
                            )
                            ->when(
                                $data['minimum_salary_required_to'],
                                fn (Builder $query, $amount): Builder => $query->where('minimum_salary_required', '<=', $amount),
                            );
                    })
                    ->indicator(function (array $data): ?string {
                        if (!$data['minimum_salary_required_from'] && !$data['minimum_salary_required_to']) {
                            return null;
                        }

                        if ($data['minimum_salary_required_from'] && $data['minimum_salary_required_to']) {
                            return 'Salary: ' . $data['minimum_salary_required_from'] . ' to ' . $data['minimum_salary_required_to'];
                        }

                        return $data['minimum_salary_required_from']
                            ? 'Salary from: ' . $data['minimum_salary_required_from']
                            : 'Salary to: ' . $data['minimum_salary_required_to'];
                    }),

                Tables\Filters\SelectFilter::make('loan_category')
                    ->label('Loan Category')
                    ->options([
                        'short_term' => 'Short Term (1-12 months)',
                        'medium_term' => 'Medium Term (13-36 months)',
                        'long_term' => 'Long Term (37+ months)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value']) {
                            'short_term' => $query->where('repayment_months', '<=', 12),
                            'medium_term' => $query->whereBetween('repayment_months', [13, 36]),
                            'long_term' => $query->where('repayment_months', '>=', 37),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->tooltip('Edit loan type')
                        ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])),
                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->visible(fn () => Auth::user()->hasRole('super_admin')),
                    Tables\Actions\Action::make('clone')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->tooltip('Clone loan type')
                        ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel']))
                        ->action(function (LoanType $record) {
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
                            // Add export logic here
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create New Loan Type')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EmployeeLoansRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoanTypes::route('/'),
            'create' => Pages\CreateLoanType::route('/create'),
            'edit' => Pages\EditLoanType::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // No role-based filtering needed for loan types as they are administrative data
        return $query;
    }
}
