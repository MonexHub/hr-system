<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LoanTypeResource\Pages;
use App\Filament\Admin\Resources\LoanTypeResource\RelationManagers;
use App\Filament\Resources\LoanTypeResource\RelationManagers\EmployeeLoansRelationManager;
use App\Models\LoanType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoanTypeResource extends Resource
{
    protected static ?string $model = LoanType::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Payroll Management';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

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
                            ->unique(ignorable: fn ($record) => $record),
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
                            ->minValue(0),

                        Forms\Components\TextInput::make('max_amount_cap')
                            ->required()
                            ->numeric()
                            ->prefix('TSh')
                            ->label('Maximum Amount')
                            ->helperText('Maximum loan amount that can be issued')
                            ->minValue(0),

                        Forms\Components\TextInput::make('repayment_months')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->suffix('months')
                            ->helperText('Duration of loan repayment period'),
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
                    ->icon('heroicon-o-tag'),

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('minimum_salary_required')
                    ->money('TZS')
                    ->label('Min. Salary')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('max_amount_cap')
                    ->money('TZS')
                    ->label('Max. Amount')
                    ->sortable()
                    ->alignRight()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('repayment_months')
                    ->suffix(' months')
                    ->sortable()
                    ->icon('heroicon-o-clock')
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
                    ]),
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
                    Tables\Actions\Action::make('clone')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function (LoanType $record) {
                            $clone = $record->replicate();
                            $clone->name = "Copy of {$record->name}";
                            $clone->code = "{$record->code}-COPY";
                            $clone->save();
                        }),
                ])
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
}
