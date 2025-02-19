<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmployeeFlatDataResource\Pages;
use App\Filament\Admin\Resources\EmployeeFlatDataResource\RelationManagers;
use App\Filament\Imports\EmployeeFlatDataImporter;
use App\Models\EmployeeFlatData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeFlatDataResource extends Resource
{
    protected static ?string $model = EmployeeFlatData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('job_title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employment_status')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Your filters here
            ])
            ->actions([
                // Your row actions here
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(EmployeeFlatDataImporter::class)
                    ->label('Import Employees')
                    ->icon('heroicon-o-arrow-up-tray')
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
            'index' => Pages\ListEmployeeFlatData::route('/'),
            'create' => Pages\CreateEmployeeFlatData::route('/create'),
            'edit' => Pages\EditEmployeeFlatData::route('/{record}/edit'),
        ];
    }
}
