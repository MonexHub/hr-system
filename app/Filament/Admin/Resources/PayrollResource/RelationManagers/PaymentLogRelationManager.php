<?php

namespace App\Filament\Admin\Resources\PayrollResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentLogRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentLog';
    protected static ?string $recordTitleAttribute = 'reference_number';
    protected static ?string $title = 'Payment Log';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reference_number')
                    ->required()
                    ->maxLength(255)
                    ->disabled(),

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix(config('payroll.currency_symbol', '$'))
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->disabled(),

                Forms\Components\KeyValue::make('response_data')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money(config('payroll.currency', 'USD')),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->headerActions([
                // No header actions needed as payment logs are created by the system
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions needed for payment logs
            ]);
    }
}
