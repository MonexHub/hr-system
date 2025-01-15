<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JobApplicationResource\Pages;
use App\Models\JobApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JobApplicationResource extends Resource
{
    protected static ?string $model = JobApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Application Details')
                ->schema([
                    Forms\Components\TextInput::make('application_number')
                        ->default('APP-' . uniqid())
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\Select::make('job_posting_id')
                        ->relationship('jobPosting', 'title')
                        ->required()
                        ->preload()
                        ->searchable(),

                    Forms\Components\Select::make('candidate_id')
                        ->relationship('candidate', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                        ->required()
                        ->preload()
                        ->searchable(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'submitted' => 'Submitted',
                            'under_review' => 'Under Review',
                            'shortlisted' => 'Shortlisted',
                            'rejected' => 'Rejected',
                            'interview_scheduled' => 'Interview Scheduled',
                            'interview_completed' => 'Interview Completed',
                            'offer_made' => 'Offer Made',
                            'offer_accepted' => 'Offer Accepted',
                            'offer_declined' => 'Offer Declined',
                            'withdrawn' => 'Withdrawn',
                            'hired' => 'Hired'
                        ])
                        ->required()
                        ->native(false),
                ])->columns(2),

            Forms\Components\Section::make('Documents')
                ->schema([
                    Forms\Components\FileUpload::make('cover_letter_path')
                        ->label('Cover Letter')
                        ->directory('applications/cover-letters')
                        ->preserveFilenames()
                        ->maxSize(5120)
                        ->downloadable(),

                    Forms\Components\Repeater::make('additional_documents')
                        ->schema([
                            Forms\Components\FileUpload::make('document')
                                ->required()
                                ->preserveFilenames()
                                ->directory('applications/documents'),
                            Forms\Components\TextInput::make('description')
                                ->required(),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['description'] ?? null),
                ]),

            Forms\Components\Section::make('Screening & Interview')
                ->schema([
                    Forms\Components\Repeater::make('screening_answers')
                        ->schema([
                            Forms\Components\TextInput::make('question')
                                ->required(),
                            Forms\Components\Textarea::make('answer')
                                ->required(),
                        ])
                        ->collapsible()
                        ->collapsed(),

                    Forms\Components\Textarea::make('interview_feedback')
                        ->label('Interview Notes & Feedback')
                        ->columnSpanFull(),

                    Forms\Components\Repeater::make('assessment_results')
                        ->schema([
                            Forms\Components\TextInput::make('skill')
                                ->required(),
                            Forms\Components\Select::make('rating')
                                ->options([
                                    1 => 'Poor',
                                    2 => 'Fair',
                                    3 => 'Good',
                                    4 => 'Very Good',
                                    5 => 'Excellent'
                                ])
                                ->required(),
                            Forms\Components\Textarea::make('notes'),
                        ])
                        ->collapsible()
                        ->collapsed(),
                ]),

            Forms\Components\Section::make('Notes & Decisions')
                ->schema([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->visible(fn (callable $get) => $get('status') === 'rejected'),

                    Forms\Components\Select::make('reviewed_by')
                        ->relationship('reviewer', 'name')
                        ->preload(),

                    Forms\Components\DateTimePicker::make('reviewed_at')
                        ->native(false),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('application_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jobPosting.title')
                    ->label('Position')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Candidate')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'gray',
                        'under_review' => 'info',
                        'shortlisted' => 'warning',
                        'interview_scheduled', 'interview_completed' => 'warning',
                        'offer_made' => 'success',
                        'offer_accepted', 'hired' => 'success',
                        'rejected', 'withdrawn', 'offer_declined' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied On')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'shortlisted' => 'Shortlisted',
                        'rejected' => 'Rejected',
                        'interview_scheduled' => 'Interview Scheduled',
                        'interview_completed' => 'Interview Completed',
                        'offer_made' => 'Offer Made',
                        'offer_accepted' => 'Offer Accepted',
                        'offer_declined' => 'Offer Declined',
                        'withdrawn' => 'Withdrawn',
                        'hired' => 'Hired'
                    ]),

                Tables\Filters\SelectFilter::make('job_posting')
                    ->relationship('jobPosting', 'title'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('schedule_interview')
                    ->form([
                        Forms\Components\DateTimePicker::make('interview_date')
                            ->required(),
                        Forms\Components\TextInput::make('location')
                            ->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (JobApplication $record, array $data): void {
                        // Handle interview scheduling
                        $record->update([
                            'status' => 'interview_scheduled',
                            'interview_details' => $data,
                        ]);
                    })
                    ->visible(fn (JobApplication $record): bool =>
                    in_array($record->status, ['submitted', 'under_review', 'shortlisted'])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('change_status')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'under_review' => 'Under Review',
                                    'shortlisted' => 'Shortlisted',
                                    'rejected' => 'Rejected',
                                ])
                                ->required(),
                            Forms\Components\Textarea::make('notes')
                                ->label('Status Change Notes'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'status' => $data['status'],
                                    'status_notes' => $data['notes'],
                                ]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListJobApplications::route('/'),
            'create' => Pages\CreateJobApplication::route('/create'),
            'view' => Pages\ViewJobApplication::route('/{record}'),
            'edit' => Pages\EditJobApplication::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'submitted')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'submitted')->exists() ? 'warning' : null;
    }
}
