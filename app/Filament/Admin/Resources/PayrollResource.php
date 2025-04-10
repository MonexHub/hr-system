<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PayrollResource\Pages;
use App\Filament\Admin\Resources\PayrollResource\RelationManagers;
use App\Models\Payroll;
use App\Services\PayrollService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Payroll Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'reference_number';
    protected static ?string $modelLabel = 'Payroll';
    protected static ?string $pluralModelLabel = 'Payrolls';

    public static function getNavigationBadge(): ?string
    {
        // Only show pending count for users who can see multiple payrolls
        if (Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])) {
            return static::getModel()::where('status', 'pending')->count();
        } elseif (Auth::user()->hasRole('department_head')) {
            // For department heads, only show pending count for their department
            $departmentId = Auth::user()->employee->department_id ?? null;

            if ($departmentId) {
                return static::getModel()::whereHas('employee', function (Builder $query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })->where('status', 'pending')->count();
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
        // Allow access for roles that should see payroll information
        return Auth::user()->hasAnyRole([
            'super_admin',
            'hr_manager',
            'financial_personnel',
            'department_head',
            'employee'
        ]);
    }

    public static function canView(Payroll|\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = Auth::user();

        // Super admin, HR, and finance can view any payroll
        if ($user->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])) {
            return true;
        }

        // Department heads can view payrolls for employees in their department
        if ($user->hasRole('department_head')) {
            $departmentId = $user->employee->department_id ?? null;
            return $departmentId && $record->employee->department_id === $departmentId;
        }

        // Regular employees can only view their own payrolls
        if ($user->hasRole('employee')) {
            return $user->employee && $record->employee_id === $user->employee->id;
        }

        return false;
    }

    public static function canCreate(): bool
    {
        // Only HR, finance, and super admin can create payrolls
        return Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel']);
    }

    public static function canEdit(Payroll|\Illuminate\Database\Eloquent\Model $record): bool
    {
        // Only HR, finance, and super admin can edit payrolls
        return Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel']);
    }

    public static function canDelete(Payroll|\Illuminate\Database\Eloquent\Model $record): bool
    {
        // Only super admin can delete payrolls
        return Auth::user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payroll Information')
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
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn ($context) => $context === 'edit')
                                    ->required()
                                    ->label('Employee')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('first_name')
                                            ->required(),
                                        Forms\Components\TextInput::make('last_name')
                                            ->required(),
                                    ]),

                                Forms\Components\DatePicker::make('period')
                                    ->label('Payroll Period')
                                    ->required()
                                    ->default(now()->startOfMonth())
                                    ->disabled(fn ($context) => $context === 'edit')
                                    ->helperText('Payroll period is set to the first day of the month')
                                    ->closeOnDateSelection(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('gross_salary')
                                    ->label('Gross Salary')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('TSH')
                                    ->numeric(),

                                Forms\Components\TextInput::make('total_benefits')
                                    ->label('Total Benefits')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('TSH')
                                    ->numeric(),

                                Forms\Components\TextInput::make('total_deductions')
                                    ->label('Total Deductions')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('TSH')
                                    ->numeric(),

                                Forms\Components\TextInput::make('net_pay')
                                    ->label('Net Pay')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('TSH')
                                    ->numeric(),
                            ]),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending')
                            ->helperText('Set the current status of this payroll')
                            ->disabled(fn () => !Auth::user()->hasAnyRole(['super_admin', 'financial_personnel']))
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->description(fn (Payroll $record): string => $record->employee->employee_id ?? '')
                    ->copyable(),

                Tables\Columns\TextColumn::make('period')
                    ->label('Period')
                    ->date('F Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->iconPosition(IconPosition::Before),

                Tables\Columns\TextColumn::make('gross_salary')
                    ->label('Gross')
                    ->money('TSH')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_benefits')
                    ->label('Benefits')
                    ->money('TSH')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_deductions')
                    ->label('Deductions')
                    ->money('TSH')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('net_pay')
                    ->label('Net Pay')
                    ->money('TSH')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('TSH'),
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'paid',
                        'heroicon-o-x-circle' => 'cancelled',
                    ])
                    ->iconPosition(IconPosition::Before)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('period', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ])
                    ->indicator('Status'),

                Tables\Filters\Filter::make('period')
                    ->form([
                        Forms\Components\DatePicker::make('period_from')
                            ->label('From')
                            ->closeOnDateSelection(),
                        Forms\Components\DatePicker::make('period_until')
                            ->label('Until')
                            ->closeOnDateSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['period_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('period', '>=', $date),
                            )
                            ->when(
                                $data['period_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('period', '<=', $date),
                            );
                    })
                    ->indicator(function (array $data): ?string {
                        if (!$data['period_from'] && !$data['period_until']) {
                            return null;
                        }

                        if ($data['period_from'] && $data['period_until']) {
                            return 'Period: ' . $data['period_from'] . ' to ' . $data['period_until'];
                        }

                        return $data['period_from']
                            ? 'Period from: ' . $data['period_from']
                            : 'Period until: ' . $data['period_until'];
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Edit payroll')
                    ->visible(fn (Payroll $record) => Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'financial_personnel'])),

                Tables\Actions\Action::make('download_payslip')
                    ->label('Payslip')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->tooltip('Download payslip as PDF')
                    ->action(function (Payroll $record) {
                        $payrollService = app(PayrollService::class);
                        return $payrollService->generatePayslipPDF($record);
                    }),

                Tables\Actions\Action::make('process_payment')
                    ->label('Process Payment')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->tooltip('Process this payment now')
                    ->visible(fn (Payroll $record) =>
                        $record->status === 'pending' &&
                        Auth::user()->hasAnyRole(['super_admin', 'financial_personnel'])
                    )
                    ->action(function (Payroll $record, PayrollService $payrollService) {
                        try {
                            $success = $payrollService->processPayment($record);

                            if ($success) {
                                Notification::make()
                                    ->title('Payment processed successfully')
                                    ->success()
                                    ->send();

                                return redirect()->back();
                            } else {
                                Notification::make()
                                    ->title('Failed to process payment')
                                    ->danger()
                                    ->send();

                                return redirect()->back();
                            }
                        } catch (\Exception $e) {
                            // Check if the error message contains "bank" or "financial" to provide
                            // a helpful action button
                            $message = $e->getMessage();
                            $notification = Notification::make()
                                ->title('Payment Processing Error')
                                ->body($message)
                                ->danger()
                                ->persistent();

                            // Add a button to edit employee if the error relates to bank details
                            if (str_contains(strtolower($message), 'bank') ||
                                str_contains(strtolower($message), 'financial') ||
                                str_contains(strtolower($message), 'account') ||
                                str_contains(strtolower($message), 'missing')) {
                                $notification->actions([
                                    \Filament\Notifications\Actions\Action::make('view_employee')
                                        ->label('Update Employee Details')
                                        ->url(route('filament.admin.resources.employees.edit', $record->employee_id))
                                        ->button(),
                                ]);
                            }

                            $notification->send();
                            return redirect()->back();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Process Payment')
                    ->modalDescription('Are you sure you want to process this payment? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, process payment'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('process_payments')
                        ->label('Process Selected Payments')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'financial_personnel']))
                        ->action(function (PayrollService $payrollService, Collection $records) {
                            $results = $records->map(function ($record) use ($payrollService) {
                                try {
                                    if ($record->status !== 'pending') {
                                        return [
                                            'employee' => $record->employee->full_name,
                                            'success' => false,
                                            'error' => 'Only pending payments can be processed',
                                        ];
                                    }

                                    $success = $payrollService->processPayment($record);
                                    return [
                                        'employee' => $record->employee->full_name,
                                        'success' => $success,
                                    ];
                                } catch (\Exception $e) {
                                    return [
                                        'employee' => $record->employee->full_name,
                                        'success' => false,
                                        'error' => $e->getMessage(),
                                        'employee_id' => $record->employee_id,
                                    ];
                                }
                            });

                            $successCount = $results->where('success', true)->count();
                            $failCount = $results->where('success', false)->count();

                            if ($failCount > 0) {
                                // Get error messages for failed payments
                                $failedEmployees = $results
                                    ->where('success', false)
                                    ->take(3) // Limit to first few for cleaner UI
                                    ->map(fn ($item) => "• {$item['employee']}: {$item['error']}")
                                    ->join("\n");

                                if ($results->where('success', false)->count() > 3) {
                                    $failedEmployees .= "\n• ... and " . ($results->where('success', false)->count() - 3) . " more";
                                }

                                // Find first bank-related error if any
                                $bankErrorItem = $results->where('success', false)->first(function ($item) {
                                    return str_contains(strtolower($item['error'] ?? ''), 'bank') ||
                                        str_contains(strtolower($item['error'] ?? ''), 'financial') ||
                                        str_contains(strtolower($item['error'] ?? ''), 'account');
                                });

                                $notification = Notification::make()
                                    ->title("Processed $successCount payments successfully. $failCount payments failed.")
                                    ->body($failedEmployees)
                                    ->warning()
                                    ->persistent();

                                // Add button to first employee with missing bank details
                                if ($bankErrorItem && isset($bankErrorItem['employee_id'])) {
                                    $notification->actions([
                                        \Filament\Notifications\Actions\Action::make('view_employee')
                                            ->label('Update Employee Details')
                                            ->url(route('filament.admin.resources.employees.edit', $bankErrorItem['employee_id']))
                                            ->button(),
                                    ]);
                                }

                                $notification->send();
                            } else {
                                Notification::make()
                                    ->title("All $successCount payments processed successfully")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Process Selected Payments')
                        ->modalDescription('Are you sure you want to process the selected payments? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, process all selected')
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('bulk_download')
                        ->label('Download Payslips')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (PayrollService $payrollService, Collection $records) {
                            return $payrollService->generateBulkPayslipsPDF($records);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentLogRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
            'generate' => Pages\GeneratePayroll::route('/generate'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['employee']);

        $user = Auth::user();

        // Apply role-based filters
        if ($user->hasRole('employee')) {
            // Employees can only see their own payrolls
            $employeeId = $user->employee->id ?? null;
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            } else {
                // If for some reason employee relationship is missing, show nothing
                $query->where('id', 0);
            }
        } elseif ($user->hasRole('department_head')) {
            // Department heads can only see payrolls for their department
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
        // HR, super admin, and financial see everything (no filter)

        return $query;
    }
}
