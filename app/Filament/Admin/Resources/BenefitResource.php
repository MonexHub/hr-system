<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BenefitResource\Pages;
use App\Filament\Admin\Resources\BenefitResource\RelationManagers;
use App\Models\Benefit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BenefitResource extends Resource
{
    protected static ?string $model = Benefit::class;
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Payroll Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(50)
                            ->unique(Benefit::class, 'code', ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->columnSpan('full'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'fixed' => 'Fixed Amount',
                                'percentage' => 'Percentage of Salary',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('value')
                            ->numeric()
                            ->required()
                            ->placeholder(fn (callable $get) => $get('type') === 'percentage' ? '10.00 (%)' : '5000.00'),
                        Forms\Components\Toggle::make('applies_to_all')
                            ->label('Applies to all employees')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn (string $state): string =>
                    match($state) {
                        'fixed' => 'Fixed Amount',
                        'percentage' => 'Percentage',
                        default => $state,
                    }
                    ),
                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(fn (string $state, Benefit $record): string =>
                    $record->type === 'percentage' ? "{$state}%" : number_format($state, 2)
                    ),
                Tables\Columns\IconColumn::make('applies_to_all')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'fixed' => 'Fixed Amount',
                        'percentage' => 'Percentage',
                    ]),
                Tables\Filters\TernaryFilter::make('applies_to_all')
                    ->label('Universal Benefits'),
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
            'index' => Pages\ListBenefits::route('/'),
            'create' => Pages\CreateBenefit::route('/create'),
            'edit' => Pages\EditBenefit::route('/{record}/edit'),
        ];
    }
}
