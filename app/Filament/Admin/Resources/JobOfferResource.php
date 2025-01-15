<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JobOfferResource\Pages;
use App\Models\JobOffer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JobOfferResource extends Resource
{
    protected static ?string $model = JobOffer::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Basic Information')
                ->schema([
                    Forms\Components\TextInput::make('offer_number')
                        ->default('OFF-' . uniqid())
                        ->required()
                        ->maxLength(255)
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\Select::make('job_application_id')
                        ->relationship('jobApplication', 'application_number')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\Select::make('candidate_id')
                                ->relationship('candidate', 'first_name')
                                ->required(),
                            Forms\Components\Select::make('job_posting_id')
                                ->relationship('jobPosting', 'title')
                                ->required(),
                        ]),
                ])
                ->columns(2),

            Forms\Components\Section::make('Offer Details')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('base_salary')
                                ->required()
                                ->numeric()
                                ->prefix(fn (callable $get) => match($get('salary_currency')) {
                                    'TZS' => 'TSh',
                                    'USD' => '$',
                                    'EUR' => '€',
                                    'GBP' => '£',
                                    'KES' => 'KSh',
                                    'UGX' => 'USh',
                                    'RWF' => 'RF',
                                    default => 'TSh',
                                })
                                ->step(1000)
                                ->minValue(0)
                                ->suffixAction(
                                    Forms\Components\Actions\Action::make('calculate')
                                        ->icon('heroicon-m-calculator')
                                ),
                        ]),

                    Forms\Components\Repeater::make('benefits_package')
                        ->schema([
                            Forms\Components\TextInput::make('benefit')
                                ->required(),
                            Forms\Components\TextInput::make('value')
                                ->required(),
                            Forms\Components\Textarea::make('description')
                                ->rows(2),
                        ])
                        ->columnSpanFull()
                        ->collapsible(),

                    Forms\Components\Repeater::make('additional_allowances')
                        ->schema([
                            Forms\Components\TextInput::make('allowance')
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->numeric()
                                ->required()
                                ->prefix('$'),
                            Forms\Components\TextInput::make('frequency')
                                ->required(),
                        ])
                        ->columnSpanFull()
                        ->collapsible(),

                    Forms\Components\DatePicker::make('proposed_start_date')
                        ->required()
                        ->minDate(now()->addWeeks(1)),

                    Forms\Components\DatePicker::make('valid_until')
                        ->required()
                        ->minDate(now())
                        ->afterOrEqual('proposed_start_date'),
                ]),

            Forms\Components\Section::make('Additional Terms')
                ->schema([
                    Forms\Components\RichEditor::make('additional_terms')
                        ->toolbarButtons([
                            'bold',
                            'bulletList',
                            'orderedList',
                            'h2',
                            'h3',
                        ])
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('special_conditions')
                        ->toolbarButtons([
                            'bold',
                            'bulletList',
                            'orderedList',
                        ])
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Status & Notes')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'pending_approval' => 'Pending Approval',
                            'approved' => 'Approved',
                            'sent' => 'Sent to Candidate',
                            'accepted' => 'Accepted',
                            'negotiating' => 'Under Negotiation',
                            'rejected' => 'Rejected',
                            'expired' => 'Expired',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('internal_notes')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('rejection_reason')
                        ->rows(3)
                        ->columnSpanFull()
                        ->visible(fn (callable $get) => $get('status') === 'rejected'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('offer_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jobApplication.candidate.full_name')
                    ->label('Candidate')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jobApplication.jobPosting.title')
                    ->label('Position')
                    ->searchable(),

                Tables\Columns\TextColumn::make('base_salary')
                    ->formatStateUsing(fn ($record) => match($record->salary_currency) {
                        'TZS' => number_format($record->base_salary, 0) . ' TSh',
                        'USD' => '$' . number_format($record->base_salary, 2),
                        'EUR' => '€' . number_format($record->base_salary, 2),
                        'GBP' => '£' . number_format($record->base_salary, 2),
                        'KES' => 'KSh ' . number_format($record->base_salary, 2),
                        'UGX' => 'USh ' . number_format($record->base_salary, 0),
                        'RWF' => 'RF ' . number_format($record->base_salary, 0),
                        default => number_format($record->base_salary, 0) . ' TSh',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending_approval' => 'warning',
                        'approved' => 'success',
                        'sent' => 'info',
                        'accepted' => 'success',
                        'negotiating' => 'warning',
                        'rejected' => 'danger',
                        'expired' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('proposed_start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved',
                        'sent' => 'Sent to Candidate',
                        'accepted' => 'Accepted',
                        'negotiating' => 'Under Negotiation',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ]),

                Tables\Filters\Filter::make('salary_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('from')
                                    ->numeric()
                                    ->label('From'),
                                Forms\Components\TextInput::make('until')
                                    ->numeric()
                                    ->label('Until'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $amount): Builder => $query->where('base_salary', '>=', $amount),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $amount): Builder => $query->where('base_salary', '<=', $amount),
                            );
                    }),

                Tables\Filters\Filter::make('valid_date_range')
                    ->form([
                        Forms\Components\DatePicker::make('valid_from'),
                        Forms\Components\DatePicker::make('valid_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['valid_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('valid_until', '>=', $date),
                            )
                            ->when(
                                $data['valid_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('valid_until', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('send')
                        ->icon('heroicon-o-paper-airplane')
                        ->requiresConfirmation()
                        ->action(fn (JobOffer $record) => $record->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]))
                        ->visible(fn (JobOffer $record): bool => $record->status === 'approved'),

                    Tables\Actions\Action::make('approve')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(fn (JobOffer $record) => $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]))
                        ->visible(fn (JobOffer $record): bool => $record->status === 'pending_approval'),

                    Tables\Actions\Action::make('mark_accepted')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Forms\Components\DatePicker::make('accepted_date')
                                ->required()
                                ->default(now()),
                            Forms\Components\Textarea::make('notes')
                                ->label('Acceptance Notes'),
                        ])
                        ->action(function (JobOffer $record, array $data): void {
                            $record->update([
                                'status' => 'accepted',
                                'responded_at' => $data['accepted_date'],
                                'negotiation_history' => array_merge(
                                    $record->negotiation_history ?? [],
                                    [['type' => 'accepted', 'date' => $data['accepted_date'], 'notes' => $data['notes']]]
                                ),
                            ]);
                        })
                        ->visible(fn (JobOffer $record): bool => $record->status === 'sent'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListJobOffers::route('/'),
            'create' => Pages\CreateJobOffer::route('/create'),
            'view' => Pages\ViewJobOffer::route('/{record}'),
            'edit' => Pages\EditJobOffer::route('/{record}/edit'),
        ];
    }
}
