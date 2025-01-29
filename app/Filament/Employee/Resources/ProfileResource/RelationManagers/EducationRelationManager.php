<?php

namespace App\Filament\Employee\Resources\ProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;

class EducationRelationManager extends RelationManager
{
    protected static string $relationship = 'education';
    protected static ?string $title = 'Academic Background';
    protected static ?string $recordTitleAttribute = 'degree';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Education Details')
                    ->description('Add your academic qualifications')
                    ->schema([
                        Forms\Components\TextInput::make('institution')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('University of Dar es Salaam')
                            ->autocomplete('organization')
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('degree')
                                    ->required()
                                    ->label('Qualification Level')
                                    ->options([
                                        'Secondary Education' => [
                                            'CSEE' => 'Certificate of Secondary Education (CSEE)',
                                            'ACSEE' => 'Advanced Certificate of Secondary Education (ACSEE)',
                                        ],
                                        'Technical Education' => [
                                            'NTA Level 4' => 'Basic Technician Certificate (NTA 4)',
                                            'NTA Level 5' => 'Technician Certificate (NTA 5)',
                                            'NTA Level 6' => 'Ordinary Diploma (NTA 6)',
                                            'NTA Level 7' => 'Higher Diploma (NTA 7)',
                                            'NTA Level 8' => 'Bachelors Degree (NTA 8)',
                                        ],
                                        'Bachelor Degree' => [
                                            'BA' => 'Bachelor of Arts (BA)',
                                            'BSc' => 'Bachelor of Science (BSc)',
                                            'BEd' => 'Bachelor of Education (BEd)',
                                            'BBA' => 'Bachelor of Business Administration (BBA)',
                                            'LLB' => 'Bachelor of Laws (LLB)',
                                            'BEng' => 'Bachelor of Engineering (BEng)',
                                            'BCom' => 'Bachelor of Commerce (BCom)',
                                        ],
                                        'Postgraduate' => [
                                            'PGD' => 'Postgraduate Diploma',
                                            'MA' => 'Master of Arts (MA)',
                                            'MSc' => 'Master of Science (MSc)',
                                            'MBA' => 'Master of Business Administration (MBA)',
                                            'MEd' => 'Master of Education (MEd)',
                                            'LLM' => 'Master of Laws (LLM)',
                                            'PhD' => 'Doctor of Philosophy (PhD)',
                                        ],
                                        'Professional' => [
                                            'CPA' => 'Certified Public Accountant (CPA)',
                                            'ACCA' => 'Association of Chartered Certified Accountants (ACCA)',
                                            'Teacher Certificate' => 'Teacher Grade IIIA Certificate',
                                            'Diploma Education' => 'Diploma in Education',
                                        ],
                                    ])
                                    ->searchable()
                                    ->native(false)
                                    ->placeholder('Select qualification level'),

                                Forms\Components\Select::make('field_of_study')
                                    ->required()
                                    ->label('Field of Study')
                                    ->options([
                                        'Education' => [
                                            'Science Education' => 'Science Education',
                                            'Arts Education' => 'Arts Education',
                                            'Primary Education' => 'Primary Education',
                                            'Secondary Education' => 'Secondary Education',
                                        ],
                                        'Business & Management' => [
                                            'Accounting' => 'Accounting',
                                            'Banking and Finance' => 'Banking and Finance',
                                            'Business Administration' => 'Business Administration',
                                            'Human Resource Management' => 'Human Resource Management',
                                            'Marketing' => 'Marketing',
                                            'Procurement' => 'Procurement and Supply Chain',
                                        ],
                                        'Science & Technology' => [
                                            'Computer Science' => 'Computer Science',
                                            'Information Technology' => 'Information Technology',
                                            'Software Engineering' => 'Software Engineering',
                                            'Telecommunications' => 'Telecommunications',
                                            'Electronics' => 'Electronics and Communications',
                                        ],
                                        'Engineering' => [
                                            'Civil Engineering' => 'Civil Engineering',
                                            'Mechanical Engineering' => 'Mechanical Engineering',
                                            'Electrical Engineering' => 'Electrical Engineering',
                                            'Mining Engineering' => 'Mining Engineering',
                                        ],
                                        'Health Sciences' => [
                                            'Medicine' => 'Medicine',
                                            'Nursing' => 'Nursing',
                                            'Pharmacy' => 'Pharmacy',
                                            'Laboratory Science' => 'Laboratory Science',
                                        ],
                                        'Agriculture' => [
                                            'Agricultural Science' => 'Agricultural Science',
                                            'Animal Science' => 'Animal Science',
                                            'Food Science' => 'Food Science',
                                            'Agribusiness' => 'Agribusiness',
                                        ],
                                        'Social Sciences' => [
                                            'Economics' => 'Economics',
                                            'Sociology' => 'Sociology',
                                            'Political Science' => 'Political Science',
                                            'Development Studies' => 'Development Studies',
                                        ],
                                        'Law' => [
                                            'Law' => 'Law',
                                            'International Law' => 'International Law',
                                        ],
                                    ])
                                    ->searchable()
                                    ->native(false)
                                    ->placeholder('Select field of study'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->required()
                                    ->maxDate(now())
                                    ->displayFormat('Y-m-d')
                                    ->native(false)
                                    ->closeOnDateSelection(),

                                Forms\Components\DatePicker::make('end_date')
                                    ->displayFormat('Y-m-d')
                                    ->maxDate(now())
                                    ->native(false)
                                    ->closeOnDateSelection()
                                    ->nullable()
                                    ->hidden(fn (Forms\Get $get) => $get('currently_enrolled')),
                            ]),

                        Forms\Components\Toggle::make('currently_enrolled')
                            ->label('Currently Enrolled')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('end_date', null);
                                }
                            }),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('grade')
                                            ->label('Grade')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->step(0.01)
                                            ->suffix('%')
                                            ->placeholder('85.50')
                                            ->hint('Enter grade as percentage (e.g., First Class: 70-100%, Upper Second: 60-69%, Lower Second: 50-59%, Pass: 40-49%)')
                                            ->nullable(),

                                        Forms\Components\Select::make('grade_display')
                                            ->label('Grade Division')
                                            ->options([
                                                '70-100' => 'First Class (70-100%)',
                                                '60-69' => 'Upper Second (60-69%)',
                                                '50-59' => 'Lower Second (50-59%)',
                                                '40-49' => 'Pass (40-49%)',
                                            ])
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                $gradeRanges = [
                                                    '70-100' => 85,
                                                    '60-69' => 65,
                                                    '50-59' => 55,
                                                    '40-49' => 45,
                                                ];
                                                if ($state && isset($gradeRanges[$state])) {
                                                    $set('grade', $gradeRanges[$state]);
                                                }
                                            })
                                            ->native(false)
                                            ->placeholder('Select grade division'),
                                    ]),

                                Forms\Components\Textarea::make('achievements')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->placeholder('Notable academic achievements, honors, or projects')
                                    ->columnSpan(2),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('institution')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->tooltip(fn ($record) => "Institution: {$record->institution}")
                    ->description(fn ($record) => $record->field_of_study)
                    ->icon('heroicon-o-academic-cap')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => str($state)->title()),

                Tables\Columns\TextColumn::make('degree')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn ($state) => str($state)->title())
                    ->color(fn ($record) => match(true) {
                        str_contains($record->degree, 'PhD') => 'success',
                        str_contains($record->degree, 'Master') => 'warning',
                        str_contains($record->degree, 'Bachelor') => 'info',
                        str_contains($record->degree, 'Diploma') => 'gray',
                        str_contains($record->degree, 'Certificate') => 'danger',
                        default => 'primary',
                    })
                    ->icon(fn ($record) => match(true) {
                        str_contains($record->degree, 'PhD') => 'heroicon-o-star',
                        str_contains($record->degree, 'Master') => 'heroicon-o-academic-cap',
                        str_contains($record->degree, 'Bachelor') => 'heroicon-o-book-open',
                        str_contains($record->degree, 'Diploma') => 'heroicon-o-document-text',
                        str_contains($record->degree, 'Certificate') => 'heroicon-o-document',
                        default => 'heroicon-o-bookmark',
                    }),

                Tables\Columns\TextColumn::make('field_of_study')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => str($state)->title()),

                Tables\Columns\TextColumn::make('grade')
                    ->label('Performance')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'N/A';
                        $grade = number_format($state, 2);
                        $division = match(true) {
                            $state >= 70 => 'First Class',
                            $state >= 60 => 'Upper Second',
                            $state >= 50 => 'Lower Second',
                            $state >= 40 => 'Pass',
                            default => 'Fail'
                        };
                        return "{$grade}% ({$division})";
                    })
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($state) => match(true) {
                        $state >= 70 => 'success',
                        $state >= 60 => 'warning',
                        $state >= 50 => 'info',
                        $state >= 40 => 'gray',
                        default => 'danger'
                    }),

                Tables\Columns\TextColumn::make('date_range')
                    ->label('Duration')
                    ->formatStateUsing(function ($record): string {
                        $start = Carbon::parse($record->start_date)->format('M Y');
                        $end = $record->end_date ? Carbon::parse($record->end_date)->format('M Y') : 'Present';
                        $duration = $record->end_date
                            ? Carbon::parse($record->start_date)->diffForHumans($record->end_date, true)
                            : Carbon::parse($record->start_date)->diffForHumans(now(), true);

                        return "{$start} - {$end} ({$duration})";
                    })
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('start_date', $direction))
                    ->tooltip(fn ($record) => "Started: " . Carbon::parse($record->start_date)->format('d M Y')),

                Tables\Columns\IconColumn::make('current')
                    ->label('Status')
                    ->state(fn ($record) => is_null($record->end_date))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->trueColor('success')
                    ->falseIcon('heroicon-o-x-circle')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->size('lg')
                    ->tooltip(fn ($record) => is_null($record->end_date)
                        ? 'Currently pursuing'
                        : 'Completed: ' . Carbon::parse($record->end_date)->format('d M Y')
                    ),

                Tables\Columns\TextColumn::make('achievements')
                    ->label('Achievements')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('degree_level')
                    ->label('Degree Level')
                    ->options([
                        'Certificate' => 'Certificate Level',
                        'Diploma' => 'Diploma Level',
                        'Bachelor' => 'Bachelor Level',
                        'Master' => 'Master Level',
                        'PhD' => 'PhD Level'
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) return $query;
                        return $query->where('degree', 'like', "%{$data['value']}%");
                    }),

                Tables\Filters\Filter::make('current_studies')
                    ->label('Currently Pursuing')
                    ->query(fn (Builder $query): Builder => $query->whereNull('end_date'))
                    ->toggle(),

                Tables\Filters\Filter::make('high_achievers')
                    ->label('High Achievers')
                    ->query(fn (Builder $query): Builder => $query->where('grade', '>=', 70))
                    ->toggle(),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->tooltip('Modify this entry')
                        ->modalWidth('2xl'),
                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Remove this entry')
                        ->modalHeading('Delete Education Record')
                        ->modalDescription('Are you sure you want to delete this education record? This action cannot be undone.')
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Actions')
                    ->iconButton()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Education')
                    ->modalWidth('2xl')
                    ->createAnother(false)
                    ->icon('heroicon-m-plus')
                    ->button()
                    ->color('primary'),
            ])
            ->emptyStateHeading('No educational records found')
            ->emptyStateDescription('Click the "Add Education" button to begin')
            ->emptyStateIcon('heroicon-o-book-open')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Education')
                    ->button()
                    ->icon('heroicon-m-plus')
                    ->color('primary'),
            ])
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }

    protected function getCountries(): array
    {
        return [
            'US' => 'United States',
            'CA' => 'Canada',
            'GB' => 'United Kingdom',
            'AU' => 'Australia',
        ];
    }
}
