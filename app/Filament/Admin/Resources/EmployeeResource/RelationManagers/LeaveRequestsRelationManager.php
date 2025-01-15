<?php

namespace App\Filament\Admin\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'leaveRequests';

    protected static ?string $title = 'Leave History';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('leave_type_id')
                ->relationship('leaveType', 'name')
                ->required()
                ->preload()
                ->reactive()
                ->afterStateUpdated(fn ($state, callable $set) =>
                $set('max_days', \App\Models\LeaveType::find($state)?->max_days ?? 0)),

            Forms\Components\DatePicker::make('start_date')
                ->required()
                ->afterOrEqual('today')
                ->reactive()
                ->afterStateUpdated(fn ($state, callable $set, $get) =>
                $this->calculateDays($state, $get('end_date'), $set)),

            Forms\Components\DatePicker::make('end_date')
                ->required()
                ->afterOrEqual('start_date')
                ->reactive()
                ->afterStateUpdated(fn ($state, callable $set, $get) =>
                $this->calculateDays($get('start_date'), $state, $set)),

            Forms\Components\TextInput::make('days_taken')
                ->numeric()
                ->disabled(),

            Forms\Components\RichEditor::make('reason')
                ->required()
                ->toolbarButtons([
                    'bold',
                    'bulletList',
                ]),

            Forms\Components\FileUpload::make('attachments')
                ->multiple()
                ->directory('leave-attachments')
                ->maxSize(5120)
                ->acceptedFileTypes(['application/pdf', 'image/*']),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Type')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Period')
                    ->formatStateUsing(fn ($record) =>
                        $record->start_date->format('d/m/Y') . ' - ' .
                        $record->end_date->format('d/m/Y'))
                    ->description(fn ($record) => "{$record->days_taken} days")
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->label('Paid'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->relationship('leaveType', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, string $model): mixed {
                        $data['status'] = 'pending';
                        return $model::create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function calculateDays($startDate, $endDate, callable $set): void
    {
        if ($startDate && $endDate) {
            $days = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;
            $set('days_taken', $days);
        }
    }
}
