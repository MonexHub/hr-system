<?php

namespace App\Filament\Admin\Resources\CandidateResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Collection;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    protected static ?string $recordTitleAttribute = 'application_number';

    protected static ?string $title = 'Job Applications';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('job_posting_id')
                    ->relationship('jobPosting', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),

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
                    ->required(),

                Forms\Components\FileUpload::make('cover_letter_path')
                    ->label('Cover Letter')
                    ->directory('applications/cover-letters'),

                Forms\Components\Repeater::make('additional_documents')
                    ->schema([
                        Forms\Components\FileUpload::make('document')
                            ->directory('applications/documents'),
                        Forms\Components\TextInput::make('description')
                            ->required(),
                    ])
                    ->collapsible(),

                Forms\Components\Textarea::make('rejection_reason')
                    ->visible(fn (callable $get) => $get('status') === 'rejected')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('interview_feedback')
                    ->visible(fn (callable $get) => in_array($get('status'), ['interview_completed', 'offer_made', 'hired']))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
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

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
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

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('applied_from')
                            ->label('Applied From'),
                        Forms\Components\DatePicker::make('applied_until')
                            ->label('Applied Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['applied_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['applied_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->beforeFormFilled(function (array $data) {
                        return array_merge($data, [
                            'application_number' => 'APP-' . uniqid(),
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download_cv')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Model $record) => $record->cover_letter_path ? storage_url($record->cover_letter_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Model $record) => $record->cover_letter_path !== null),
                Tables\Actions\Action::make('schedule_interview')
                    ->icon('heroicon-o-calendar')
                    ->form([
                        Forms\Components\DateTimePicker::make('interview_date')
                            ->required(),
                        Forms\Components\TextInput::make('location')
                            ->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (Model $record, array $data): void {
                        // Handle interview scheduling
                        $record->update([
                            'status' => 'interview_scheduled',
                            'interview_details' => $data,
                        ]);
                    })
                    ->visible(fn (Model $record): bool => in_array($record->status, ['submitted', 'under_review', 'shortlisted'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('change_status')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
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
}
