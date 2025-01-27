<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LeaveTypeResource\Pages;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Leave Management';

    protected static ?string $navigationLabel = 'Leave Types';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Leave Type Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),

                    Forms\Components\Select::make('category')
                        ->options([
                            'annual' => 'Annual Leave',
                            'sick' => 'Sick Leave',
                            'maternity' => 'Maternity Leave',
                            'paternity' => 'Paternity Leave',
                            'study' => 'Study Leave',
                            'unpaid' => 'Unpaid Leave',
                            'other' => 'Other',
                        ])
                        ->required(),

                    Forms\Components\ColorPicker::make('color')
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Leave Configuration')
                ->schema([
                    Forms\Components\TextInput::make('days_per_year')
                        ->label('Default Days Per Year')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('max_consecutive_days')
                        ->label('Maximum Consecutive Days')
                        ->helperText('Maximum days that can be taken at once. Leave empty for no limit.')
                        ->numeric()
                        ->nullable(),

                    Forms\Components\TextInput::make('min_request_days')
                        ->label('Minimum Request Days')
                        ->helperText('Minimum days required when requesting this leave type')
                        ->numeric()
                        ->default(1)
                        ->required(),

                    Forms\Components\TextInput::make('notice_days')
                        ->label('Required Notice Days')
                        ->helperText('How many days in advance the leave should be requested')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\Toggle::make('requires_attachment')
                        ->label('Requires Document Attachment')
                        ->helperText('Whether this leave type requires supporting documents'),

                    Forms\Components\Toggle::make('is_paid')
                        ->label('Is Paid Leave')
                        ->default(true),

                    Forms\Components\Toggle::make('affects_salary')
                        ->label('Affects Salary Calculation'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])
                ->columns(2),

            Forms\Components\Section::make('Additional Settings')
                ->schema([
                    Forms\Components\Toggle::make('allow_half_day')
                        ->label('Allow Half Day')
                        ->default(false),

                    Forms\Components\Toggle::make('allow_negative_balance')
                        ->label('Allow Negative Balance')
                        ->default(false),

                    Forms\Components\Toggle::make('requires_approval')
                        ->label('Requires Approval')
                        ->default(true),

                    Forms\Components\Select::make('gender_restriction')
                        ->label('Gender Restriction')
                        ->options([
                            'none' => 'None',
                            'male' => 'Male Only',
                            'female' => 'Female Only',
                        ])
                        ->default('none'),

                    Forms\Components\TextInput::make('qualifying_months')
                        ->label('Qualifying Service (Months)')
                        ->helperText('Minimum months of service required to be eligible')
                        ->numeric()
                        ->default(0),
                ])
                ->columns(2),

            Forms\Components\Section::make('Description & Policies')
                ->schema([
                    Forms\Components\RichEditor::make('description')
                        ->toolbarButtons([
                            'bold',
                            'bulletList',
                            'orderedList',
                        ])
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('policies')
                        ->label('Special Policies/Rules')
                        ->rows(3)
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category')
                    ->badge(),

                Tables\Columns\ColorColumn::make('color'),

                Tables\Columns\TextColumn::make('days_per_year')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean(),

                Tables\Columns\IconColumn::make('requires_attachment')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'annual' => 'Annual Leave',
                        'sick' => 'Sick Leave',
                        'maternity' => 'Maternity Leave',
                        'paternity' => 'Paternity Leave',
                        'study' => 'Study Leave',
                        'unpaid' => 'Unpaid Leave',
                        'other' => 'Other',
                    ]),

                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Paid Leave'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveTypes::route('/'),
            'create' => Pages\CreateLeaveType::route('/create'),
            'edit' => Pages\EditLeaveType::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('view_any_leave_type');
    }
}
