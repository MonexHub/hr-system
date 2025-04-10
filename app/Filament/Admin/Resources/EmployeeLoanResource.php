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

class EmployeeLoanResource extends Resource
{
    protected static ?string $model = EmployeeLoan::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Payroll Management';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship(
                                'employee',
                                'first_name',
                                fn (Builder $query) => $query->select(['id', 'first_name', 'last_name'])
                            )->searchable()->preload(true),

                        Forms\Components\Select::make('loan_type_id')
                            ->label('Loan Type')
                            ->options(LoanType::query()->pluck('name', 'id'))
                            ->required(
                            )->preload(true)
                            ->searchable(),

                        Forms\Components\TextInput::make('amount_requested')
                            ->label('Amount Requested')
                            ->required()
                            ->numeric()
                            ->prefix(config('payroll.currency_symbol', '$'))
                            ->columnSpan('full'),

                        Forms\Components\TextInput::make('amount_approved')
                            ->label('Amount Approved')
                            ->numeric()
                            ->prefix(config('payroll.currency_symbol', '$')),

                        Forms\Components\TextInput::make('monthly_installment')
                            ->label('Monthly Installment')
                            ->numeric()
                            ->prefix(config('payroll.currency_symbol', '$')),

                        Forms\Components\DatePicker::make('repayment_start_date')
                            ->label('Repayment Start Date')
                            ->required(),

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
                            ->default('pending'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpan('full'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('loanType.name')
                    ->label('Loan Type')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount_requested')
                    ->label('Requested')
                    ->money(config('payroll.currency', 'USD'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_approved')
                    ->label('Approved')
                    ->money(config('payroll.currency', 'USD'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('monthly_installment')
                    ->label('Monthly Payment')
                    ->money(config('payroll.currency', 'USD')),

                Tables\Columns\TextColumn::make('repayment_start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => ['approved', 'in_repayment'],
                        'danger' => 'rejected',
                        'primary' => 'completed',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
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
                    ]),

                Tables\Filters\SelectFilter::make('loan_type_id')
                    ->label('Loan Type')
                    ->relationship('loanType', 'name'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
}
