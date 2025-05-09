<?php

namespace App\Filament\Resources\LoanTypeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Collection;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmployeeLoansRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeLoans';

    protected static ?string $recordTitleAttribute = 'reference_number';

    protected static ?string $title = 'Employee Loans';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Loan Details')
                    ->description('Enter the basic loan information')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\TextInput::make('reference_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('LN-2025-0001')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('employee_id')
                            ->required()
                            ->numeric()
                            ->label('Employee ID'),

                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('TSh')
                            ->minValue(1)
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'disbursed' => 'Disbursed',
                            ])
                            ->required()
                            ->default('pending')
                            ->columnSpan(1),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Additional Information')
                    ->description('Enter additional loan details')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\DatePicker::make('application_date')
                            ->required()
                            ->default(now())
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('approval_date')
                            ->nullable()
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('disbursement_date')
                            ->nullable()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('repayment_period')
                            ->numeric()
                            ->required()
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->repayment_months;
                            })
                            ->suffix('months')
                            ->columnSpan(1),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Remarks')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Textarea::make('remarks')
                            ->nullable()
                            ->maxLength(65535)
                            ->placeholder('Enter any additional notes or remarks about this loan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_number')
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->icon('heroicon-o-identification'),

                Tables\Columns\TextColumn::make('employee_id')
                    ->searchable()
                    ->sortable()
                    ->label('Employee ID')
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('amount')
                    ->money('TZS')
                    ->sortable()
                    ->color('success')
                    ->alignRight(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'rejected',
                        'warning' => 'pending',
                        'success' => 'approved',
                        'primary' => 'disbursed',
                    ]),

                Tables\Columns\TextColumn::make('application_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('approval_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('disbursement_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created At'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'disbursed' => 'Disbursed',
                    ]),

                Tables\Filters\Filter::make('amount')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->label('Amount From')
                            ->numeric()
                            ->prefix('TSh'),
                        Forms\Components\TextInput::make('amount_to')
                            ->label('Amount To')
                            ->numeric()
                            ->prefix('TSh'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
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
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->modalHeading('Create New Employee Loan')
                    ->using(function (RelationManager $livewire, array $data): Model {
                        // Add loan_type_id to the data
                        $data['loan_type_id'] = $livewire->ownerRecord->id;
                        return $livewire->getRelationship()->create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil'),

                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash'),

                    Tables\Actions\Action::make('approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Model $record): bool => $record->status === 'pending')
                        ->action(function (Model $record): void {
                            $record->update([
                                'status' => 'approved',
                                'approval_date' => now(),
                            ]);
                        }),

                    Tables\Actions\Action::make('reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reject Loan Application')
                        ->modalDescription('Are you sure you want to reject this loan application?')
                        ->modalSubmitActionLabel('Yes, reject it')
                        ->visible(fn (Model $record): bool => $record->status === 'pending')
                        ->action(function (Model $record): void {
                            $record->update([
                                'status' => 'rejected',
                            ]);
                        }),

                    Tables\Actions\Action::make('disburse')
                        ->icon('heroicon-o-banknotes')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->visible(fn (Model $record): bool => $record->status === 'approved')
                        ->action(function (Model $record): void {
                            $record->update([
                                'status' => 'disbursed',
                                'disbursement_date' => now(),
                            ]);
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function ($record): void {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'approved',
                                        'approval_date' => now(),
                                    ]);
                                }
                            });
                        }),
                ]),
            ])
            ->emptyStateHeading('No Employee Loans')
            ->emptyStateDescription('Start creating employee loans for this loan type.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
