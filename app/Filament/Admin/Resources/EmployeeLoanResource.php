<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmployeeLoanResource\Pages;
use App\Filament\Admin\Resources\EmployeeLoanResource\RelationManagers;
use App\Models\Employee;
use App\Models\EmployeeLoan;
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
use Filament\Notifications\Notification;

class EmployeeLoanResource extends Resource
{
    protected static ?string $model = EmployeeLoan::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Payroll Management';
    protected static ?int $navigationSort = 5;
    protected static ?string $modelLabel = 'Employee Loan';
    protected static ?string $pluralModelLabel = 'Employee Loans';
    protected static ?string $recordTitleAttribute = 'employee.full_name';

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['super_admin', 'financial_personnel'])) {
            // Show pending approvals count
            return static::getModel()::where('status', 'pending')->count();
        } elseif ($user->hasRole('department_head')) {
            // Show pending for their department
            $departmentId = $user->employee->department_id ?? null;
            if ($departmentId) {
                return static::getModel()::where('status', 'pending')
                    ->whereHas('employee', fn ($query) => $query->where('department_id', $departmentId))
                    ->count();
            }
        } elseif ($user->hasRole('employee')) {
            // Show their own pending loans
            $employeeId = $user->employee->id ?? null;
            if ($employeeId) {
                return static::getModel()::where('status', 'pending')
                    ->where('employee_id', $employeeId)
                    ->count();
            }
        }

        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->hasAnyRole([
            'super_admin',
            'hr_manager',
            'financial_personnel',
            'department_head',
            'employee'
        ]);
    }

    public static function canView(EmployeeLoan|\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = Auth::user();

        // Super admin, HR, and finance can view any loan
        if ($user->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])) {
            return true;
        }

        // Department heads can view loans for employees in their department
        if ($user->hasRole('department_head')) {
            $departmentId = $user->employee->department_id ?? null;
            return $departmentId && $record->employee->department_id === $departmentId;
        }

        // Regular employees can only view their own loans
        if ($user->hasRole('employee')) {
            return $user->employee && $record->employee_id === $user->employee->id;
        }

        return false;
    }

    public static function canCreate(): bool
    {
        // HR, finance, super admin, and employees can create loans
        return Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel', 'employee']);
    }

    public static function canEdit(EmployeeLoan|\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = Auth::user();

        // Super admin, HR, and finance can edit any loan
        if ($user->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])) {
            return true;
        }

        // Employees can only edit their own pending loans
        if ($user->hasRole('employee')) {
            return $user->employee && $record->employee_id === $user->employee->id && $record->status === 'pending';
        }

        return false;
    }

    public static function canDelete(EmployeeLoan|\Illuminate\Database\Eloquent\Model $record): bool
    {
        // Only super admin and HR can delete loans
        return Auth::user()->hasAnyRole(['super_admin', 'hr_manager']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Loan Information')
                    ->description('Define the loan details')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('employee_id')
                                    ->relationship(
                                        'employee',
                                        'first_name',
                                        function (Builder $query) {
                                            $user = Auth::user();

                                            // Restrict employee selection based on role
                                            if ($user->hasRole('department_head')) {
                                                $departmentId = $user->employee->department_id ?? null;
                                                if ($departmentId) {
                                                    $query->where('department_id', $departmentId);
                                                }
                                            } elseif ($user->hasRole('employee')) {
                                                $employeeId = $user->employee->id ?? null;
                                                if ($employeeId) {
                                                    $query->where('id', $employeeId);
                                                }
                                            }

                                            return $query->select(['id', 'first_name', 'last_name']);
                                        }
                                    )
                                    ->label('Employee')
                                    ->required()
                                    ->searchable()
                                    ->preload(true)
                                    ->disabled(fn ($context) => $context === 'edit')
                                    ->columnSpan(2),

                                Forms\Components\Select::make('loan_type_id')
                                    ->label('Loan Type')
                                    ->options(LoanType::query()->pluck('name', 'id'))
                                    ->required()
                                    ->preload(true)
                                    ->searchable()
                                    ->live()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('amount_requested')
                                    ->label('Amount Requested')
                                    ->required()
                                    ->numeric()
                                    ->prefix('TSh')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0) : null)
                                    ->dehydrateStateUsing(fn ($state) => str_replace(',', '', $state))
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('amount_approved')
                                    ->label('Amount Approved')
                                    ->numeric()
                                    ->prefix('TSh')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0) : null)
                                    ->dehydrateStateUsing(fn ($state) => $state ? str_replace(',', '', $state) : null)
                                    ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])),

                                Forms\Components\TextInput::make('monthly_installment')
                                    ->label('Monthly Installment')
                                    ->numeric()
                                    ->prefix('TSh')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0) : null)
                                    ->dehydrateStateUsing(fn ($state) => $state ? str_replace(',', '', $state) : null)
                                    ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])),

                                Forms\Components\DatePicker::make('repayment_start_date')
                                    ->label('Repayment Start Date')
                                    ->required()
                                    ->closeOnDateSelection()
                                    ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending Approval',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'in_repayment' => 'In Repayment',
                                        'completed' => 'Completed',
                                    ])
                                    ->required()
                                    ->default('pending')
                                    ->native(false)
                                    ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Notes')
                                    ->maxLength(1000)
                                    ->rows(3),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (EmployeeLoan $record): string => $record->employee->employee_id ?? '')
                    ->copyable(),

                Tables\Columns\TextColumn::make('loanType.name')
                    ->label('Loan Type')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('amount_requested')
                    ->label('Requested')
                    ->money('TSH')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('amount_approved')
                    ->label('Approved')
                    ->money('TSH')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('monthly_installment')
                    ->label('Monthly Payment')
                    ->money('TSH')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('repayment_start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->iconPosition(IconPosition::Before),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => ['approved', 'in_repayment'],
                        'danger' => 'rejected',
                        'primary' => 'completed',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-check-badge' => 'in_repayment',
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-check-circle-2' => 'completed',
                    ])
                    ->iconPosition(IconPosition::Before),

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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'in_repayment' => 'In Repayment',
                        'completed' => 'Completed',
                    ])
                    ->indicator('Status'),

                Tables\Filters\SelectFilter::make('loan_type_id')
                    ->label('Loan Type')
                    ->relationship('loanType', 'name')
                    ->indicator('Loan Type'),

                Tables\Filters\Filter::make('created_at')
                    ->label('Application Date')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\DatePicker::make('created_from')
                                ->label('From')
                                ->closeOnDateSelection(),
                            Forms\Components\DatePicker::make('created_until')
                                ->label('Until')
                                ->closeOnDateSelection(),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicator(function (array $data): ?string {
                        if (!$data['created_from'] && !$data['created_until']) {
                            return null;
                        }

                        if ($data['created_from'] && $data['created_until']) {
                            return 'Applied: ' . $data['created_from'] . ' to ' . $data['created_until'];
                        }

                        return $data['created_from']
                            ? 'Applied from: ' . $data['created_from']
                            : 'Applied until: ' . $data['created_until'];
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->tooltip('Edit loan')
                        ->visible(fn (EmployeeLoan $record) => self::canEdit($record)),

                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->visible(fn (EmployeeLoan $record) => self::canDelete($record)),

                    Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (EmployeeLoan $record) =>
                            $record->status === 'pending' &&
                            Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])
                        )
                        ->form([
                            Forms\Components\TextInput::make('amount_approved')
                                ->label('Amount to Approve')
                                ->required()
                                ->numeric()
                                ->prefix('TSh'),
                            Forms\Components\TextInput::make('monthly_installment')
                                ->label('Monthly Installment')
                                ->required()
                                ->numeric()
                                ->prefix('TSh'),
                            Forms\Components\DatePicker::make('repayment_start_date')
                                ->label('Repayment Start Date')
                                ->required(),
                        ])
                        ->action(function (EmployeeLoan $record, array $data): void {
                            $record->update([
                                'amount_approved' => $data['amount_approved'],
                                'monthly_installment' => $data['monthly_installment'],
                                'repayment_start_date' => $data['repayment_start_date'],
                                'status' => 'approved',
                            ]);

                            Notification::make()
                                ->title('Loan Approved')
                                ->body("Loan #{$record->id} has been approved for {$record->employee->full_name}")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (EmployeeLoan $record) =>
                            $record->status === 'pending' &&
                            Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])
                        )
                        ->requiresConfirmation()
                        ->action(function (EmployeeLoan $record): void {
                            $record->update(['status' => 'rejected']);

                            Notification::make()
                                ->title('Loan Rejected')
                                ->body("Loan #{$record->id} has been rejected")
                                ->warning()
                                ->send();
                        }),
                ])
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])),

                    Tables\Actions\BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            // Export logic here
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create New Loan')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel', 'employee'])),
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
            'index' => Pages\ListEmployeeLoans::route('/'),
            'create' => Pages\CreateEmployeeLoan::route('/create'),
            'edit' => Pages\EditEmployeeLoan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['employee', 'loanType']);

        $user = Auth::user();

        // Apply role-based filters
        if ($user->hasRole('employee')) {
            // Employees can only see their own loans
            $employeeId = $user->employee->id ?? null;
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            } else {
                // If for some reason employee relationship is missing, show nothing
                $query->where('id', 0);
            }
        } elseif ($user->hasRole('department_head')) {
            // Department heads can only see loans for their department
            $departmentId = $user->employee->department_id ?? null;
            if ($departmentId) {
                $query->whereHas('employee', function (Builder $query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                });
            } else {
                // If department is missing, show nothing
                $query->where('id', 0);
            }
        }
        return $query;
    }
}
