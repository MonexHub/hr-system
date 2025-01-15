<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InterviewScheduleResource\Pages;
use App\Models\InterviewSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InterviewScheduleResource extends Resource
{
    protected static ?string $model = InterviewSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Interview Details')
                ->schema([
                    Forms\Components\Select::make('job_application_id')
                        ->relationship('jobApplication', 'application_number')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\Select::make('job_posting_id')
                                ->relationship('jobPosting', 'title')
                                ->required(),
                            Forms\Components\Select::make('candidate_id')
                                ->relationship('candidate', 'first_name')
                                ->required(),
                        ]),

                    Forms\Components\TextInput::make('round_number')
                        ->required()
                        ->numeric()
                        ->default(1),

                    Forms\Components\Select::make('interviewer_id')
                        ->relationship('interviewer', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\DateTimePicker::make('scheduled_at')
                        ->required()
                        ->timezone(config('app.timezone'))
                        ->minutesStep(15),

                    Forms\Components\TextInput::make('duration_minutes')
                        ->required()
                        ->numeric()
                        ->default(60),
                ])
                ->columns(2),

            Forms\Components\Section::make('Interview Format')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->options([
                            'phone_screening' => 'Phone Screening',
                            'technical' => 'Technical Interview',
                            'hr' => 'HR Interview',
                            'culture_fit' => 'Culture Fit',
                            'final' => 'Final Interview',
                        ])
                        ->required(),

                    Forms\Components\Select::make('mode')
                        ->options([
                            'phone' => 'Phone Call',
                            'video' => 'Video Call',
                            'in_person' => 'In Person',
                        ])
                        ->required()
                        ->reactive(),

                    Forms\Components\TextInput::make('location')
                        ->required()
                        ->label(fn (callable $get) => match ($get('mode')) {
                            'in_person' => 'Location',
                            'video' => 'Meeting Link',
                            'phone' => 'Phone Number',
                            default => 'Location/Link'
                        }),

                    Forms\Components\Textarea::make('notes')
                        ->rows(3)
                        ->label('Interview Instructions'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Interview Questions')
                ->schema([
                    Forms\Components\Repeater::make('interview_questions')
                        ->schema([
                            Forms\Components\TextInput::make('question')
                                ->required(),
                            Forms\Components\Select::make('category')
                                ->options([
                                    'technical' => 'Technical Skills',
                                    'experience' => 'Work Experience',
                                    'behavioral' => 'Behavioral',
                                    'cultural' => 'Cultural Fit',
                                ])
                                ->required(),
                            Forms\Components\Textarea::make('notes')
                                ->rows(2),
                        ])
                        ->defaultItems(3)
                        ->collapsible()
                        ->grid(2),
                ]),

            Forms\Components\Section::make('Feedback & Results')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'scheduled' => 'Scheduled',
                            'confirmed' => 'Confirmed',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            'rescheduled' => 'Rescheduled',
                            'no_show' => 'No Show',
                        ])
                        ->required(),

                    Forms\Components\Textarea::make('feedback')
                        ->rows(3)
                        ->visible(fn (callable $get) => $get('status') === 'completed'),

                    Forms\Components\TextInput::make('rating')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(5)
                        ->visible(fn (callable $get) => $get('status') === 'completed'),

                    Forms\Components\Textarea::make('recommendations')
                        ->rows(3)
                        ->visible(fn (callable $get) => $get('status') === 'completed'),

                    Forms\Components\Textarea::make('cancellation_reason')
                        ->rows(3)
                        ->visible(fn (callable $get) => in_array($get('status'), ['cancelled', 'rescheduled'])),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jobApplication.application_number')
                    ->label('Application')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('candidate_name')
                    ->label('Candidate')
                    ->searchable(['candidate.first_name', 'candidate.last_name'])
                    ->getStateUsing(fn ($record) => $record->jobApplication->candidate->full_name),

                Tables\Columns\TextColumn::make('type')
                    ->badge(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('interviewer.name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_person' => 'success',
                        'video' => 'info',
                        'phone' => 'warning',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'confirmed' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'rescheduled' => 'warning',
                        'no_show' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('rating')
                    ->sortable()
                    ->visible(fn ($livewire) => $livewire instanceof Pages\ListInterviewSchedules),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'rescheduled' => 'Rescheduled',
                        'no_show' => 'No Show',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'phone_screening' => 'Phone Screening',
                        'technical' => 'Technical Interview',
                        'hr' => 'HR Interview',
                        'culture_fit' => 'Culture Fit',
                        'final' => 'Final Interview',
                    ]),

                Tables\Filters\Filter::make('scheduled_at')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_from'),
                        Forms\Components\DatePicker::make('scheduled_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['scheduled_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '>=', $date),
                            )
                            ->when(
                                $data['scheduled_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('record_feedback')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->form([
                        Forms\Components\Textarea::make('feedback')
                            ->required(),
                        Forms\Components\TextInput::make('rating')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(5),
                        Forms\Components\Textarea::make('recommendations'),
                    ])
                    ->action(function (InterviewSchedule $record, array $data): void {
                        $record->update([
                            'status' => 'completed',
                            'feedback' => $data['feedback'],
                            'rating' => $data['rating'],
                            'recommendations' => $data['recommendations'],
                        ]);
                    })
                    ->visible(fn (InterviewSchedule $record): bool =>
                    in_array($record->status, ['confirmed', 'scheduled'])
                    ),
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
            'index' => Pages\ListInterviewSchedules::route('/'),
            'create' => Pages\CreateInterviewSchedule::route('/create'),
            'view' => Pages\ViewInterviewSchedule::route('/{record}'),
            'edit' => Pages\EditInterviewSchedule::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('scheduled_at', today())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->count() ?: null;
    }
}
