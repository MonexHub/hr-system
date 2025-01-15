<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\LeaveRequestResource\Pages;
use App\Filament\Employee\Resources\LeaveRequestResource\RelationManagers;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;


class LeaveRequestResource extends Resource
{

    protected static ?string $model = LeaveRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Leave Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Select::make('leave_type_id')
                        ->label('Leave Type')
                        ->options(LeaveType::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->reactive(),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->required()
                        ->minDate(now())
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if ($state && $get('end_date')) {
                                $start = Carbon::parse($state);
                                $end = Carbon::parse($get('end_date'));
                                $days = $start->diffInDays($end) + 1;
                                $set('total_days', $days);
                            }
                        }),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('End Date')
                        ->required()
                        ->minDate(fn (callable $get) => $get('start_date'))
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if ($get('start_date') && $state) {
                                $start = Carbon::parse($get('start_date'));
                                $end = Carbon::parse($state);
                                $days = $start->diffInDays($end) + 1;
                                $set('total_days', $days);
                            }
                        }),

                    Forms\Components\TextInput::make('total_days')
                        ->label('Total Days')
                        ->disabled()
                        ->dehydrated()
                        ->numeric(),

                    Forms\Components\Textarea::make('reason')
                        ->label('Reason')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\FileUpload::make('attachment_path')
                        ->label('Attachment')
                        ->directory('leave-attachments')
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->maxSize(5120)
                        ->visible(fn (callable $get) =>
                            LeaveType::find($get('leave_type_id'))?->requires_attachment ?? false
                        ),

                    Forms\Components\Hidden::make('employee_id')
                        ->default(fn () => auth()->user()->employee->id),

                    Forms\Components\Hidden::make('status')
                        ->default('pending'),
                ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date(),
                Tables\Columns\TextColumn::make('total_days')
                    ->label('Days'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'rejected',
                        'warning' => 'pending',
                        'success' => 'approved',
                        'secondary' => 'cancelled'
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested On')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled'
                    ]),
                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->relationship('leaveType', 'name')
                    ->label('Leave Type'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel Request')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (LeaveRequest $record) =>
                        $record->status === 'pending'
                    )
                    ->action(fn (LeaveRequest $record) =>
                    $record->update(['status' => 'cancelled'])
                    ),
            ])
            ->modifyQueryUsing(fn (Builder $query) =>
            $query->where('employee_id', auth()->user()->employee->id)
            );
    }




    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}
