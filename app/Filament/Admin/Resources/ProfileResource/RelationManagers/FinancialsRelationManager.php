<?php

namespace App\Filament\Admin\Resources\ProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FinancialsRelationManager extends RelationManager
{
    protected static string $relationship = 'financials';
    protected static ?string $title = 'Financial Information';
    protected static ?string $recordTitleAttribute = 'bank_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bank Details')
                    ->schema([
                        Forms\Components\Select::make('bank_name')
                            ->label('Bank Name')
                            ->options([
                                'EQUITY' => 'Equity Bank',
                                'KCB' => 'KCB Bank',
                                'COOP' => 'Cooperative Bank',
                                'ABSA' => 'ABSA Bank',
                                'STANBIC' => 'Stanbic Bank',
                                'NCBA' => 'NCBA Bank',
                                'DTB' => 'Diamond Trust Bank',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('account_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('branch_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Health Insurance')
                    ->schema([
                        Forms\Components\TextInput::make('insurance_provider')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('insurance_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('insurance_expiry_date')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('NSSF Information')
                    ->schema([
                        Forms\Components\TextInput::make('nssf_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('nssf_registration_date')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bank_name')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('account_number')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('insurance_provider')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('insurance_number')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('insurance_expiry_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nssf_number')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bank_name')
                    ->options([
                        'EQUITY' => 'Equity Bank',
                        'KCB' => 'KCB Bank',
                        'COOP' => 'Cooperative Bank',
                        'ABSA' => 'ABSA Bank',
                        'STANBIC' => 'Stanbic Bank',
                        'NCBA' => 'NCBA Bank',
                        'DTB' => 'Diamond Trust Bank',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function () {
                        Notification::make()
                            ->title('Financial information added')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function () {
                        Notification::make()
                            ->title('Financial information updated')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function () {
                        Notification::make()
                            ->title('Financial information deleted')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No financial information added')
            ->emptyStateDescription('Add your financial information here.')
            ->emptyStateIcon('heroicon-o-credit-card');
    }
}
