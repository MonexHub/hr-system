<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PerformanceAppraisalResource\Pages;
use App\Helpers\AppraisalPeriodHelper;
use App\Models\Employee;
use App\Models\PerformanceAppraisal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Collection;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use IbrahimBougaoua\FilaProgress\Infolists\Components\ProgressBarEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PerformanceAppraisalResource extends Resource
{
    protected static ?string $model = PerformanceAppraisal::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Performance Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Performance Appraisal')
                ->tabs([
                    // Basic Information Tab
                    Forms\Components\Tabs\Tab::make('Basic Information')
                        ->icon('heroicon-m-user')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Section::make('Employee Details')
                                        ->schema([
                                            Forms\Components\Select::make('employee_id')
                                            ->relationship(
                                                name: 'employee',
                                                titleAttribute: 'first_name',
                                                modifyQueryUsing: fn (Builder $query) => $query->with('department'),
                                            )
                                            ->getOptionLabelFromRecordUsing(fn (Employee $record) =>
                                                "{$record->first_name} {$record->last_name}"
                                            )
                                            ->preload()
                                            ->searchable(['first_name', 'last_name'])
                                            ->required()
                                            ->live()
                                            ->disabled(fn() => Auth::user()->hasRole('employee')) // Employees cannot modify
                                            ->default(fn() => Auth::user()->hasRole('employee') ? Auth::user()->employee->id : null) // Auto-select employee
                                            ->dehydrated(fn() => true)
                                            ->afterStateUpdated(function ($state, Forms\Set $set,$livewire) {
                                                if ($state) {
                                                    $employee = Employee::with('department.manager')->find($state);
                                                    if ($employee) {
                                                        // Determine supervisor from reporting relationship or department manager
                                                        $supervisor_id = $employee->reporting_to ?? $employee->department?->manager_id;

                                                            // Ensure supervisor ID is set only if found
                                                            if ($supervisor_id) {
                                                                $set('immediate_supervisor_id', $supervisor_id);
                                                            }

                                                        // Set default dates if they're not set
                                                        $set('evaluation_date', now()->format('Y-m-d'));
                                                        $set('evaluation_period_start', now()->startOfMonth()->format('Y-m-d'));
                                                        $set('evaluation_period_end', now()->endOfMonth()->format('Y-m-d'));
                                                    }
                                                }
                                            }),

                                                Forms\Components\Select::make('immediate_supervisor_id')
                                                ->relationship(
                                                    name: 'supervisor',
                                                    titleAttribute: 'first_name',
                                                )
                                                ->getOptionLabelFromRecordUsing(fn (Employee $record) =>
                                                    "{$record->first_name} {$record->last_name}"
                                                )
                                                ->preload()
                                                ->searchable(['first_name', 'last_name'])
                                                ->required()
                                                ->disabled()
                                                ->default(fn () => Auth::user()->hasRole('employee')
                                                ? Auth::user()->employee->reporting_to ?? Auth::user()->employee->department?->manager_id
                                                : null
                                            )
                                            ->afterStateHydrated(function ($state, Forms\Set $set, $record) {
                                                if ($record) {
                                                    $employee = Employee::with('department.manager')->find($record->employee_id);
                                                    if ($employee) {
                                                        $supervisor_id = $employee->reporting_to ?? $employee->department?->manager_id;
                                                        if ($supervisor_id) {
                                                            $set('immediate_supervisor_id', $supervisor_id);
                                                        }
                                                    }
                                                }
                                            })
                                            ->dehydrated(true),
                                        ]),

                                    Forms\Components\Section::make('Evaluation Period')
                                        ->schema([
                                            Forms\Components\DatePicker::make('evaluation_date')
                                                ->required()
                                                ->label('Evaluation Date')
                                                ->default(now()),

                                            Forms\Components\DatePicker::make('evaluation_period_start')
                                                ->required()
                                                ->label('Period Start Date')
                                                ->default(now()->startOfMonth()),

                                            Forms\Components\DatePicker::make('evaluation_period_end')
                                                ->required()
                                                ->label('Period End Date')
                                                ->default(now()->endOfMonth())
                                                ->after('evaluation_period_start'),
                                        ]),
                                ]),
                        ]),
                    // Objectives Tab
                    Forms\Components\Tabs\Tab::make('Objectives')
                        ->icon('heroicon-m-list-bullet')
                        ->schema([
                            Forms\Components\Repeater::make('objectives')
                                ->relationship('objectives')
                                ->schema([
                                    Forms\Components\TextInput::make('objective')
                                        ->label('Objective Description')
                                        ->required(),
                                    Forms\Components\DatePicker::make('completion_date')
                                        ->label('Completion Date')
                                        ->required(),
                                                                        // Rating Field (Only visible to Supervisor or Super Admin)
                                Forms\Components\Select::make('rating')
                                ->options([
                                    1 => '1 - Needs Improvement',
                                    2 => '2 - Fair',
                                    3 => '3 - Good',
                                    4 => '4 - Very Good',
                                    5 => '5 - Excellent',
                                ])
                                ->label('Assessment Rating')
                                ->visible(fn() =>
                                    Auth::user()->hasRole('super_admin') ||
                                    Auth::user()->id === Auth::user()->employee?->reporting_to
                                ),

                            // Supervisor Feedback Field (Only visible to Supervisor or Super Admin)
                            Forms\Components\Textarea::make('supervisor_feedback')
                                ->label('Supervisor Feedback')
                                ->visible(fn() =>
                                    Auth::user()->hasRole('super_admin') ||
                                    Auth::user()->id === Auth::user()->employee?->reporting_to
                                ),
                                ])
                                ->createItemButtonLabel('Add Objective')
                                ->collapsible(),
                        ]),

                    // Performance Ratings Tab (Restricted)
                    // Performance Ratings Tab
                    Forms\Components\Tabs\Tab::make('Performance Ratings')
                        ->icon('heroicon-m-star')
                        ->visible(fn() => Auth::user()->hasRole('super_admin') || Auth::user()->id === Auth::user()->employee?->reporting_to)
                        ->schema([
                            Forms\Components\Section::make('Rating Guide')
                                ->description('Each competency is rated on a scale of 1-5')
                                ->schema([
                                    Forms\Components\Grid::make(5)
                                        ->schema([
                                            Forms\Components\Placeholder::make('rating_1')
                                                ->label('1 - Needs Improvement')
                                                ->content('Below Expected Performance'),

                                            Forms\Components\Placeholder::make('rating_2')
                                                ->label('2 - Fair')
                                                ->content('Approaching Standards'),

                                            Forms\Components\Placeholder::make('rating_3')
                                                ->label('3 - Good')
                                                ->content('Meets Standards'),

                                            Forms\Components\Placeholder::make('rating_4')
                                                ->label('4 - Very Good')
                                                ->content('Exceeds Standards'),

                                            Forms\Components\Placeholder::make('rating_5')
                                                ->label('5 - Excellent')
                                                ->content('Outstanding Performance'),
                                        ]),
                                ])
                                ->collapsible(),

                            Forms\Components\Section::make('Core Competencies')
                                ->description('Rate the employee on the following competencies')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Select::make('quality_of_work')
                                                ->options(self::getRatingOptions())
                                                ->required()
                                                ->label('Quality of Work')
                                                ->helperText('Accuracy, thoroughness, and quality of work produced'),

                                            Forms\Components\Select::make('productivity')
                                                ->options(self::getRatingOptions())
                                                ->required()
                                                ->label('Productivity')
                                                ->helperText('Volume of work and efficiency'),

                                            Forms\Components\Select::make('job_knowledge')
                                                ->options(self::getRatingOptions())
                                                ->required()
                                                ->label('Job Knowledge')
                                                ->helperText('Understanding of job duties and technical skills'),

                                            Forms\Components\Select::make('reliability')
                                                ->options(self::getRatingOptions())
                                                ->required()
                                                ->label('Reliability')
                                                ->helperText('Dependability and consistency'),

                                            Forms\Components\Select::make('communication')
                                                ->options(self::getRatingOptions())
                                                ->required()
                                                ->label('Communication')
                                                ->helperText('Written and verbal communication skills'),

                                            Forms\Components\Select::make('teamwork')
                                                ->options(self::getRatingOptions())
                                                ->required()
                                                ->label('Teamwork')
                                                ->helperText('Collaboration and team contribution'),
                                        ]),
                                ]),

                            Forms\Components\Section::make('Overall Rating')
                                ->schema([
                                    Forms\Components\Placeholder::make('calculated_rating')
                                        ->content(function ($get) {
                                            $scores = [
                                                $get('quality_of_work'),
                                                $get('productivity'),
                                                $get('job_knowledge'),
                                                $get('reliability'),
                                                $get('communication'),
                                                $get('teamwork'),
                                            ];

                                            $scores = array_filter($scores, fn($score) => $score !== null);

                                            if (empty($scores)) {
                                                return 'Pending Ratings';
                                            }

                                            $average = array_sum($scores) / count($scores);
                                            return number_format($average, 2);
                                        }),
                                ]),
                        ]),

                    // Comments & Feedback Tab
                    Forms\Components\Tabs\Tab::make('Comments & Feedback')
                        ->icon('heroicon-m-chat-bubble-left-right')
                        ->visible(fn() => Auth::user()->hasRole('super_admin') || Auth::user()->id === Auth::user()->employee?->reporting_to)
                        ->schema([
                            Forms\Components\Section::make('Employee Input')
                                ->schema([
                                    Forms\Components\RichEditor::make('achievements')
                                        ->label('Key Achievements')
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'bulletList',
                                            'orderedList',
                                        ]),

                                    Forms\Components\RichEditor::make('areas_for_improvement')
                                        ->label('Areas for Improvement')
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'bulletList',
                                            'orderedList',
                                        ]),
                                ]),

                            Forms\Components\Section::make('Supervisor Comments')
                                ->schema([
                                    Forms\Components\RichEditor::make('supervisor_comments')
                                        ->toolbarButtons(['bold', 'italic', 'bulletList'])
                                        ->visible(fn() => Auth::user()->can('supervisor_approve_performance::appraisal')),
                                ])
                                ->visible(fn() => Auth::user()->can('supervisor_approve_performance::appraisal')),

                            Forms\Components\Section::make('HR Comments')
                                ->schema([
                                    Forms\Components\RichEditor::make('hr_comments')
                                        ->toolbarButtons(['bold', 'italic', 'bulletList'])
                                        ->visible(fn() => Auth::user()->can('hr_approve_performance::appraisal')),
                                ])
                                ->visible(fn() => Auth::user()->can('hr_approve_performance::appraisal')),
                        ]),
                ])
                ->activeTab(0)->columnSpanFull(),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Employee')
                    ->formatStateUsing(fn ($record) =>
                        $record->employee->first_name . ' ' . $record->employee->last_name
                    )
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                    Tables\Columns\TextColumn::make('supervisor.full_name')
                    ->label('Supervisor')
                    ->formatStateUsing(fn ($record) =>
                        $record->supervisor
                            ? "{$record->supervisor->first_name} {$record->supervisor->last_name}"
                            : 'Not Assigned'
                    )
                    ->sortable()
                    ->searchable(['supervisor.first_name', 'supervisor.last_name']),

                Tables\Columns\TextColumn::make('evaluation_date')
                    ->label('Evaluation Date')
                    ->date('M d, Y')
                    ->sortable(),

                    Tables\Columns\TextColumn::make('evaluation_period')
                    ->label('Evaluation Period')
                    ->formatStateUsing(fn (PerformanceAppraisal $record): string => $record->evaluation_period)
                    ->sortable('evaluation_period_start')
                    ->searchable(),

                    Tables\Columns\TextColumn::make('overall_rating')
                    ->badge()
                    ->color(fn ($state): string => match(true) {
                        $state >= 4.5 => 'success',
                        $state >= 3.5 => 'info',
                        $state >= 2.5 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable()
                    ->visible(fn ($state) => !is_null($state)),

                    Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->trashed() ? 'danger' : match ($record->status) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'supervisor_approved' => 'info',
                        'hr_approved', 'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($record) => $record->trashed() ? 'Deleted' : ucfirst($record->status)),
                    Tables\Columns\TextColumn::make('objectives.objective')
    ->label('Objectives')
    ->limit(30),
Tables\Columns\TextColumn::make('objectives.rating')
    ->label('Average Rating')
    ->formatStateUsing(fn ($state) => number_format($state, 2))
    ->sortable()
    ->visible(fn ($state) => !is_null($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'supervisor_approved' => 'Supervisor Approved',
                        'hr_approved' => 'HR Approved',
                        'completed' => 'Completed',
                    ]),

                Tables\Filters\Filter::make('evaluation_date')
                    ->form([
                        Forms\Components\DatePicker::make('evaluation_date_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('evaluation_date_until')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['evaluation_date_from'],
                                fn (Builder $query, $date): Builder =>
                                $query->whereDate('evaluation_date', '>=', $date),
                            )
                            ->when(
                                $data['evaluation_date_until'],
                                fn (Builder $query, $date): Builder =>
                                $query->whereDate('evaluation_date', '<=', $date),
                            );
                    }),
                    Tables\Filters\TrashedFilter::make()
                ->label('Show Deleted')
                ->visible(fn () => Auth::user()->hasRole('super_admin')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                ->visible(fn (PerformanceAppraisal $record) =>
                ($record->status === 'draft' && Auth::user()->hasRole('employee') && Auth::user()->employee?->id === $record->employee_id) ||
                Auth::user()->id === $record->employee->reporting_to ||
                Auth::user()->id === $record->employee->department?->manager_id ||
                Auth::user()->hasRole('super_admin')
            ),
                Tables\Actions\Action::make('submit')
                ->action(fn (PerformanceAppraisal $record) => $record->submit())
                ->requiresConfirmation()
                ->visible(fn (PerformanceAppraisal $record): bool =>
                    !$record->trashed() && $record->status === 'draft' &&
                    (Auth::user()->can('submit_performance::appraisal') || Auth::user()->hasRole('employee'))
                ),

            Tables\Actions\Action::make('supervisor_approve')
                ->action(fn (PerformanceAppraisal $record) => $record->supervisorApprove())
                ->requiresConfirmation()
                ->visible(fn (PerformanceAppraisal $record): bool =>
                    !$record->trashed() && $record->status === 'submitted' &&
                    (Auth::user()->id === $record->employee->reporting_to ||
                    Auth::user()->id === $record->employee->department?->manager_id ||
                    Auth::user()->hasRole('super_admin'))
                ),
                Tables\Actions\Action::make('hr_approve')
                    ->action(fn (PerformanceAppraisal $record) => $record->hrApprove())
                    ->requiresConfirmation()
                    ->visible(fn (PerformanceAppraisal $record): bool =>
                    !$record->trashed() &&  $record->status === 'supervisor_approved' &&
                        Auth::user()->can('hr_approve_performance::appraisal')),
                        Tables\Actions\Action::make('delete')
                        ->requiresConfirmation()
                        ->color('danger')
                        ->modalHeading('Delete Performance Appraisal')
                        ->modalDescription('Do you want to permanently delete this appraisal, or just mark it as deleted?')
                        ->action(fn (PerformanceAppraisal $record, Tables\Actions\Action $action) =>
                            request()->input('delete_type') === 'permanent'
                            ? ($record->forceDelete() ?? $action->successNotification('Record permanently deleted'))
                                : $record->delete()
                        )
                        ->modalSubmitActionLabel('Soft Delete')
                        ->extraModalActions([
                            Tables\Actions\Action::make('permanent_delete')
                                ->label('Permanent Delete')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->action(fn (PerformanceAppraisal $record, Tables\Actions\Action $action) =>
                                    $record->forceDelete() ?? $action->successNotification('Record permanently deleted')
                                )
                                ->after(fn () => redirect(request()->header('Referer'))) // Prevents returning to modal
                        ])
                        ->visible(fn (PerformanceAppraisal $record): bool =>
                        (Auth::user()->hasRole('employee') && $record->status === 'draft') ||
                        (!Auth::user()->hasRole('employee') && $record->status !== 'submitted')
                        ),
                        Tables\Actions\Action::make('restore')
                        ->label('Restore')
                        ->action(fn (PerformanceAppraisal $record) => $record->restore())
                        ->visible(fn (PerformanceAppraisal $record) => $record->trashed()),
            ])
            ->defaultSort('evaluation_date', 'desc');
    }

    protected static function getRatingOptions(): array
    {
        return [
            1 => '1 - Needs Improvement',
            2 => '2 - Fair',
            3 => '3 - Good',
            4 => '4 - Very Good',
            5 => '5 - Excellent',
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerformanceAppraisals::route('/'),
            'create' => Pages\CreatePerformanceAppraisal::route('/create'),
            'edit' => Pages\EditPerformanceAppraisal::route('/{record}/edit'),
        ];
    }
}
