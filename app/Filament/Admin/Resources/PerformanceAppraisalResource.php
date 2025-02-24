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
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    if ($state) {
                                                        $employee = Employee::with('department.manager')->find($state);
                                                        if ($employee) {
                                                            // Set supervisor from reporting relationship or department manager
                                                            $supervisor_id = $employee->reporting_to ?? $employee->department?->manager_id;
                                                            $set('immediate_supervisor_id', $supervisor_id);

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
                                                ->disabled(),
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

                    // Performance Ratings Tab
                    Forms\Components\Tabs\Tab::make('Performance Ratings')
                        ->icon('heroicon-m-star')
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

                Tables\Columns\TextColumn::make('immediate_supervisor_id')
                    ->label('Supervisor')
                    ->formatStateUsing(fn ($record) =>
                        $record->supervisor?->first_name . ' ' . $record->supervisor?->last_name
                    )
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('supervisor', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('evaluation_date')
                    ->label('Evaluation Date')
                    ->date('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('evaluation_period')
                    ->label('Evaluation Period')
                    ->formatStateUsing(fn (PerformanceAppraisal $record): string =>
                        optional($record->evaluation_period_start)->format('M Y') . ' - ' .
                        optional($record->evaluation_period_end)->format('M Y')
                    )
                    ->searchable(['evaluation_period_start', 'evaluation_period_end'])
                    ->sortable('evaluation_period_start'),

                Tables\Columns\TextColumn::make('overall_rating')
                    ->badge()
                    ->color(fn ($state): string => match(true) {
                        $state >= 4.5 => 'success',
                        $state >= 3.5 => 'info',
                        $state >= 2.5 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'supervisor_approved' => 'info',
                        'hr_approved', 'completed' => 'success',
                        default => 'gray',
                    }),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('submit')
                    ->action(fn (PerformanceAppraisal $record) => $record->submit())
                    ->requiresConfirmation()
                    ->visible(fn (PerformanceAppraisal $record): bool =>
                        $record->status === 'draft' &&
                        Auth::user()->can('submit_performance::appraisal')),

                Tables\Actions\Action::make('supervisor_approve')
                    ->action(fn (PerformanceAppraisal $record) => $record->supervisorApprove())
                    ->requiresConfirmation()
                    ->visible(fn (PerformanceAppraisal $record): bool =>
                        $record->status === 'submitted' &&
                        Auth::user()->can('supervisor_approve_performance::appraisal')),

                Tables\Actions\Action::make('hr_approve')
                    ->action(fn (PerformanceAppraisal $record) => $record->hrApprove())
                    ->requiresConfirmation()
                    ->visible(fn (PerformanceAppraisal $record): bool =>
                        $record->status === 'supervisor_approved' &&
                        Auth::user()->can('hr_approve_performance::appraisal')),
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
