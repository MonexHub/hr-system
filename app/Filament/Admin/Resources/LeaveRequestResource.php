<?php

namespace App\Filament\Admin\Resources;


use App\Filament\Admin\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Leave Management';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $query = static::getEloquentQuery();

        if ($user->hasRole('department_head')) {
            $query->whereHas('employee', function ($q) use ($user) {
                $q->where('department_id', $user->employee->department_id);
            })->where('status', LeaveRequest::STATUS_PENDING);
        } elseif ($user->hasRole('hr_manager')) {
            $query->where('status', LeaveRequest::STATUS_DEPARTMENT_APPROVED);
        } elseif ($user->hasRole('chief_executive_officer')) {
            $query->where('status', LeaveRequest::STATUS_HR_APPROVED);
        }

        $count = $query->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole(['super_admin', 'hr_manager']);

        return $form->schema([
            Section::make('Request Details')
                ->description('Basic information about the leave request')
                ->icon('heroicon-o-information-circle')
                ->columns(2)
                ->schema([
                    Forms\Components\Hidden::make('employee_id')
                        ->default(fn () => auth()->user()->employee?->id)
                        ->required()
                        ->dehydrated(true)
                        ->visible(!$isAdmin),

                    Forms\Components\Select::make('employee_id')
                        ->relationship(
                            'employee',
                            'first_name',
                            fn (Builder $query) => $query->select(['id', 'first_name', 'last_name'])
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->required()
                        ->visible($isAdmin)
                        ->label('Employee')
                        ->afterStateUpdated(function ($state, callable $set) {
                            static::validateEmployeeLeaveBalance($state, $set);
                        }),

                    Forms\Components\Select::make('leave_type_id')
                        ->label('Leave Type')
                        ->options(function () {
                            return LeaveType::where('is_active', true)
                                ->get()
                                ->mapWithKeys(fn ($type) => [$type->id => $type->name]);
                        })
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            static::validateLeaveTypeSelection($state, $get('employee_id'), $set);
                        }),

                    Forms\Components\DatePicker::make('start_date')
                        ->required()
                        ->minDate(now())
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            static::calculateTotalDays($state, $get('end_date'), $set);
                            static::validateDateSelection($state, $get('end_date'), $get('employee_id'));
                        }),

                    Forms\Components\DatePicker::make('end_date')
                        ->required()
                        ->minDate(fn (callable $get) => $get('start_date') ?? now())
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            static::calculateTotalDays($get('start_date'), $state, $set);
                            static::validateDateSelection($get('start_date'), $state, $get('employee_id'));
                        }),

                    Forms\Components\TextInput::make('total_days')
                        ->disabled()
                        ->dehydrated()
                        ->numeric(),

                    Forms\Components\Textarea::make('reason')
                        ->required()
                        ->maxLength(1000)
                        ->columnSpan(2),

                    Forms\Components\FileUpload::make('attachment_path')
                        ->directory('leave-attachments')
                        ->visibility('private')
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->maxSize(5120)
                        ->hidden(fn ($get) => !$get('requires_attachment'))
                        ->columnSpan(2),
                ]),

            Section::make('Approval Information')
                ->description('Approval status and remarks')
                ->icon('heroicon-o-check-circle')
                ->visible(fn ($record) => $record !== null)
                ->schema([
                    Forms\Components\View::make('filament.components.leave-approval-timeline'),

                    Forms\Components\Textarea::make('department_remarks')
                        ->label('Department Head Remarks')
                        ->disabled()
                        ->visible(fn ($record) => $record?->department_approved_at),

                    Forms\Components\Textarea::make('hr_remarks')
                        ->label('HR Remarks')
                        ->disabled()
                        ->visible(fn ($record) => $record?->hr_approved_at),

                    Forms\Components\Textarea::make('ceo_remarks')
                        ->label('CEO Remarks')
                        ->disabled()
                        ->visible(fn ($record) => $record?->ceo_approved_at),

                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->disabled()
                        ->visible(fn ($record) => $record?->isRejected()),

                    Forms\Components\Textarea::make('cancellation_reason')
                        ->label('Cancellation Reason')
                        ->disabled()
                        ->visible(fn ($record) => $record?->isCancelled()),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('employee.full_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_days')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'department_approved',
                        'info' => 'hr_approved',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'secondary' => 'cancelled',
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'department_approved' => 'Department Approved',
                        'hr_approved' => 'HR Approved',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('leave_type')
                    ->relationship('leaveType', 'name'),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // Department Head Approval Action
                Tables\Actions\Action::make('approve_department')
                    ->label('Approve (HOD)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Approval Remarks')
                            ->required(),
                    ])
                    ->visible(fn (LeaveRequest $record): bool =>
                        auth()->user()->hasRole('department_head') &&
                        $record->status === LeaveRequest::STATUS_PENDING
                    )
                    ->action(function (LeaveRequest $record, array $data): void {
                        try {
                            $record->approveDepartment(auth()->user(), $data['remarks']);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to process approval. ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),


                // HR Approval Action
                Tables\Actions\Action::make('approve_hr')
                    ->label('Approve (HR)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Approval Remarks')
                            ->required(),
                    ])
                    ->visible(fn (LeaveRequest $record): bool =>
                        auth()->user()->hasRole('hr_manager') &&
                        $record->status === LeaveRequest::STATUS_DEPARTMENT_APPROVED
                    )
                    ->action(function (LeaveRequest $record, array $data): void {
                        try {
                            $record->approveHR(auth()->user(), $data['remarks']);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to process approval. ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),


                // In LeaveRequestResource.php
                Tables\Actions\Action::make('super_admin_approve')
                    ->label('Super Admin Approval')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Approval Comments')
                            ->required()
                            ->placeholder('Enter approval rationale')
                            ->maxLength(500)
                            ->columnSpanFull()
                    ])
                    ->visible(function (LeaveRequest $record): bool {
                        $user = auth()->user();
                        return $user->hasRole('super_admin') &&
                            !$record->isApproved() &&
                            !$record->isRejected() &&
                            !$record->isCancelled();
                    })
                    ->action(function (LeaveRequest $record, array $data): void {
                        try {
                            DB::beginTransaction();

                            // Use model's force approval method
                            $record->forceApprove(
                                approver: auth()->user(),
                                remarks: $data['remarks']
                            );

                            DB::commit();

                            Notification::make()
                                ->title('Admin Override Approval')
                                ->body('Leave request force-approved successfully')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Approval Failed')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalHeading('Force Approve Leave Request')
                    ->modalDescription('This will immediately approve the request regardless of current status')
                    ->modalSubmitActionLabel('Confirm Force Approval'),



                // CEO Approval Action
                Tables\Actions\Action::make('approve_ceo')
                    ->label('Approve (CEO)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Approval Remarks')
                            ->required(),
                    ])
                    ->visible(fn (LeaveRequest $record): bool =>
                        auth()->user()->hasRole('chief_executive_officer') &&
                        $record->status === LeaveRequest::STATUS_HR_APPROVED &&
                        $record->isEmployeeDepartmentHead()
                    )
                    ->action(function (LeaveRequest $record, array $data): void {
                        try {
                            $record->approveCEO(auth()->user(), $data['remarks']);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to process approval. ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Reject Action
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required(),
                    ])
                    ->visible(fn (LeaveRequest $record): bool =>
                        auth()->user()->hasAnyRole(['department_head', 'hr_manager', 'chief_executive_officer']) &&
                        in_array($record->status, [
                            LeaveRequest::STATUS_PENDING,
                            LeaveRequest::STATUS_DEPARTMENT_APPROVED,
                            LeaveRequest::STATUS_HR_APPROVED
                        ])
                    )
                    ->action(function (LeaveRequest $record, array $data): void {
                        try {
                            DB::beginTransaction();
                            $record->reject(auth()->user(), $data['reason']);
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to reject leave request. Please try again.')
                                ->danger()
                                ->send();
                        }
                    }),

                // Cancel Action
                Tables\Actions\Action::make('cancel')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Cancellation Reason')
                            ->required(),
                    ])
                    ->visible(fn (LeaveRequest $record): bool => $record->canBeCancelled())
                    ->action(function (LeaveRequest $record, array $data): void {
                        try {
                            DB::beginTransaction();
                            $record->cancel(auth()->user(), $data['reason']);
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to cancel leave request. Please try again.')
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    protected static function validateEmployeeLeaveBalance($employeeId, callable $set): void
    {
        if (!$employeeId) return;

        $employee = \App\Models\Employee::find($employeeId);
        if (!$employee) return;

        $leaveBalances = $employee->leaveBalances()
            ->where('year', now()->year)
            ->get()
            ->mapWithKeys(function ($balance) {
                return [
                    $balance->leave_type_id => [
                        'entitled' => $balance->entitled_days + $balance->carried_forward_days + $balance->additional_days,
                        'available' => $balance->entitled_days + $balance->carried_forward_days +
                            $balance->additional_days - $balance->taken_days - $balance->pending_days
                    ]
                ];
            });$set('available_balances', $leaveBalances);
    }

    protected static function validateLeaveTypeSelection($leaveTypeId, $employeeId, callable $set): void
    {
        if (!$leaveTypeId || !$employeeId) return;

        $employee = \App\Models\Employee::find($employeeId);
        if (!$employee) return;

        $leaveType = LeaveType::find($leaveTypeId);
        if (!$leaveType) return;

        $balance = $employee->leaveBalances()
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', now()->year)
            ->first();

        if (!$balance) {
            Notification::make()
                ->title('No Leave Balance')
                ->body("No leave balance found for this leave type in the current year.")
                ->warning()
                ->send();
            return;
        }

        $availableBalance = $balance->entitled_days +
            $balance->carried_forward_days +
            $balance->additional_days -
            $balance->taken_days -
            $balance->pending_days;

        if ($availableBalance <= 0) {
            Notification::make()
                ->title('Insufficient Balance')
                ->body("You have no available balance for this leave type.")
                ->warning()
                ->send();
            return;
        }

        $set('requires_attachment', $leaveType->requires_attachment);
        $set('available_days', $availableBalance);
    }

    protected static function validateDateSelection($startDate, $endDate, $employeeId): void
    {
        if (!$startDate || !$endDate || !$employeeId) return;

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Check for overlapping leaves
        $existingLeave = LeaveRequest::where('employee_id', $employeeId)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->whereIn('status', [
                LeaveRequest::STATUS_PENDING,
                LeaveRequest::STATUS_DEPARTMENT_APPROVED,
                LeaveRequest::STATUS_HR_APPROVED,
                LeaveRequest::STATUS_APPROVED
            ])
            ->first();

        if ($existingLeave) {
            Notification::make()
                ->title('Overlapping Leave Request')
                ->body("You already have a leave request for this period ({$existingLeave->start_date->format('M d, Y')} to {$existingLeave->end_date->format('M d, Y')}).")
                ->warning()
                ->send();
        }
    }

    protected static function calculateTotalDays($startDate, $endDate, callable $set): void
    {
        if (!$startDate || !$endDate) return;

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Calculate working days excluding weekends
        $totalDays = $start->diffInDaysFiltered(function (Carbon $date) {
                return !$date->isWeekend();
            }, $end) + 1;

        $set('total_days', $totalDays);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
//            'view' => Pages\ViewLeaveRequest::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        if ($user->hasRole('department_head')) {
            return $query->where(function ($query) use ($user) {
                $query->where('employee_id', $user->employee->id)
                    ->orWhereHas('employee', function ($query) use ($user) {
                        $query->where('department_id', $user->employee->department_id);
                    });
            });
        }

        if ($user->hasRole('hr_manager')) {
            return $query;
        }

        if ($user->hasRole('chief_executive_officer')) {
            return $query->where(function ($query) use ($user) {
                $query->where('status', LeaveRequest::STATUS_HR_APPROVED)
                    ->orWhere('employee_id', $user->employee->id);
            });
        }

        return $query->where('employee_id', $user->employee->id);
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() ? 'warning' : null;
    }

    public static function canEdit(Model $record): bool
    {
        // Only allow editing for pending requests
        return $record->status === 'pending';
    }


}
