<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Collection;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Recruitment';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Candidate Information')
                ->tabs([
                    // Personal Information Tab
                    Forms\Components\Tabs\Tab::make('Personal Details')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('First Name')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('last_name')
                                    ->label('Last Name')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\Select::make('nationality')
                                    ->label('Nationality')
                                    ->options(Candidate::getNationalityOptions())
                                    ->searchable(),

                                Forms\Components\TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->prefix('+')
                                    ->maxLength(20),

                                Forms\Components\Select::make('preferred_language')
                                    ->options([
                                        'en' => 'English',
                                        'sw' => 'Swahili',
                                        'es' => 'Spanish',
                                        'fr' => 'French',
                                        'de' => 'German',
                                        'zh' => 'Chinese',
                                        'ar' => 'Arabic',
                                    ]),

                                Forms\Components\FileUpload::make('photo_path')
                                    ->label('Profile Photo')
                                    ->image()
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->directory('candidates/photos')
                                    ->maxSize(2048) // 2MB
//                                    ->validateFileSize()
                                    ->helperText('Upload image (max 2MB). Recommended: Square image.')
                                    ->rules(['image', 'mimes:jpg,jpeg,png', 'max:2048']),
                            ]),
                        ]),

                    // Professional Details Tab
                    Forms\Components\Tabs\Tab::make('Professional Details')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('current_job_title')
                                    ->label('Current Job Title')
                                    ->options([
                                        'software_engineer' => 'Software Engineer',
                                        'project_manager' => 'Project Manager',
                                        'business_analyst' => 'Business Analyst',
                                        'data_scientist' => 'Data Scientist',
                                        'designer' => 'Designer',
                                        'marketing_specialist' => 'Marketing Specialist',
                                        'sales_executive' => 'Sales Executive',
                                        'product_manager' => 'Product Manager',
                                        'other' => 'Other'
                                    ])
                                    ->searchable(),

                                Forms\Components\Select::make('years_of_experience')
                                    ->options(self::getExperienceRangeOptions()),

                                Forms\Components\Select::make('status')
                                    ->label('Application Status')
                                    ->options([
                                        'applied' => 'Applied',
                                        'screening' => 'In Screening',
                                        'shortlisted' => 'Shortlisted',
                                        'interview' => 'Interview Stage',
                                        'offer' => 'Offer Stage',
                                        'hired' => 'Hired',
                                        'rejected' => 'Rejected',
                                        'withdrawn' => 'Withdrawn'
                                    ])
                                    ->default('applied')
                                    ->required()
                                    ->native(false),

                                Forms\Components\Select::make('availability_status')
                                    ->options([
                                        'immediately' => 'Immediately Available',
                                        'notice_period' => 'Serving Notice Period',
                                        'employed' => 'Currently Employed',
                                    ]),

                                Forms\Components\TextInput::make('notice_period')
                                    ->label('Notice Period (in days)')
                                    ->numeric()
                                    ->visible(fn (callable $get) =>
                                        $get('availability_status') === 'notice_period'),
                            ]),

                            Forms\Components\RichEditor::make('professional_summary')
                                ->label('Professional Summary')
                                ->toolbarButtons([
                                    'bold',
                                    'bulletList',
                                    'orderedList',
                                ])
                                ->columnSpanFull(),

                            Forms\Components\FileUpload::make('resume_path')
                                ->label('Resume/CV')
                                ->acceptedFileTypes(['application/pdf'])
                                ->directory('candidates/resumes')
                                ->preserveFilenames()
                                ->maxSize(10240) // 10MB
                                ->downloadable()
                                ->openable()
                                ->previewable()
//                                ->validateFileSize()
                                ->helperText('Upload PDF file (max 10MB). Original filename will be preserved.')
                                ->rules(['file', 'mimes:pdf', 'max:10240']),
                        ]),

                    // Skills & Qualifications Tab
                    Forms\Components\Tabs\Tab::make('Skills & Qualifications')
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Forms\Components\TagsInput::make('skills')
                                ->label('Technical Skills')
                                ->separator(',')
                                ->suggestions(self::getCommonSkillsSuggestions()),

                            Forms\Components\TagsInput::make('languages')
                                ->label('Language Skills')
                                ->separator(',')
                                ->suggestions(self::getLanguageOptions()),

                            Forms\Components\Repeater::make('education')
                                ->label('Education History')
                                ->schema([
                                    Forms\Components\TextInput::make('institution')
                                        ->label('Institution Name')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Enter institution name'),

                                    Forms\Components\Select::make('degree_level')
                                        ->label('Degree/Qualification Level')
                                        ->options([
                                            'high_school' => 'High School',
                                            'certificate' => 'Certificate',
                                            'diploma' => 'Diploma',
                                            'associate' => 'Associate Degree',
                                            'bachelor' => 'Bachelor\'s Degree',
                                            'master' => 'Master\'s Degree',
                                            'doctorate' => 'Doctorate',
                                            'professional' => 'Professional Certification',
                                            'other' => 'Other'
                                        ])
                                        ->required()
                                        ->searchable()
                                        ->native(false),

                                    Forms\Components\TextInput::make('field_of_study')
                                        ->label('Field of Study/Major')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Enter your field of study')
                                        ->helperText('e.g., Computer Science, Business Administration, etc.'),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\DatePicker::make('start_date')
                                                ->label('Start Date')
                                                ->required()
                                                ->maxDate(now())
                                                ->displayFormat('d/m/Y'),

                                            Forms\Components\DatePicker::make('end_date')
                                                ->label('End Date (or Expected)')
                                                ->minDate(fn (callable $get) => $get('start_date'))
                                                ->displayFormat('d/m/Y')
                                                ->helperText('Leave blank if this is your current education'),
                                        ]),

                                    Forms\Components\TextInput::make('grade')
                                        ->label('Grade/GPA (Optional)')
                                        ->placeholder('Enter your grade or GPA')
                                        ->maxLength(20),

                                    Forms\Components\Textarea::make('achievements')
                                        ->label('Achievements/Notes')
                                        ->placeholder('Enter any notable achievements, honors, or additional information')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ])
                                ->itemLabel(fn (array $state): ?string =>
                                    $state['institution'] ?? null
                                    ? "{$state['degree_level']} at {$state['institution']}"
                                    : null
                                )
                                ->collapsible()
                                ->defaultItems(1)
                                ->reorderable()
                                ->columnSpanFull()
                                ->maxItems(5)
//                                ->grid(2)
                                ->columns(2)
                                ->collapsed(false)
                                ->columnSpanFull(),
                        ]),

                    // Compensation Tab
                    Forms\Components\Tabs\Tab::make('Compensation')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('current_salary')
                                    ->numeric()
                                    ->prefix(fn ($get) => $get('salary_currency') ?? '$'),

                                Forms\Components\TextInput::make('expected_salary')
                                    ->numeric()
                                    ->prefix(fn ($get) => $get('salary_currency') ?? '$')
                                    ->required(),

                                Forms\Components\Select::make('salary_currency')
                                    ->options([
                                        'USD' => 'USD ($)',
                                        'EUR' => 'EUR (€)',
                                        'GBP' => 'GBP (£)',
                                        'JPY' => 'JPY (¥)',
                                        'AUD' => 'AUD (A$)',
                                        'CAD' => 'CAD (C$)',
                                        'TZS' => 'TZS (TSh)',
                                        'ZAR' => 'ZAR (R)',
                                        'KES' => 'KES (KSh)',
                                        'NGN' => 'NGN (₦)',
                                        'EGP' => 'EGP (£E)',
                                        'MAD' => 'MAD (DH)',
                                        'GHS' => 'GHS (₵)',
                                        'XOF' => 'XOF (CFA)',
                                        'XAF' => 'XAF (CFA)',
                                        'BWP' => 'BWP (P)',
                                        'UGX' => 'UGX (USh)',
                                        'RWF' => 'RWF (FRw)',
                                        'SDG' => 'SDG (£S)',
                                        'DZD' => 'DZD (دج)',
                                        'LYD' => 'LYD (ل.د)',
                                        'MUR' => 'MUR (₨)',
                                        'SCR' => 'SCR (₨)',
                                        'SZL' => 'SZL (E)',
                                        'ZMW' => 'ZMW (ZK)',
                                        'MWK' => 'MWK (MK)',
                                        'ETB' => 'ETB (Br)',
                                        'SOS' => 'SOS (Sh)',
                                    ])
                                    ->default('TZS'),


                                Forms\Components\Select::make('salary_type')
                                    ->options([
                                        'annual' => 'Annual',
                                        'monthly' => 'Monthly',
                                        'hourly' => 'Hourly',
                                    ])
                                    ->default('annual'),
                            ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Photo')
                    ->circular(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Candidate Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('current_job_title')
                    ->label('Current Position')
                    ->searchable(),

                Tables\Columns\TextColumn::make('expected_salary')
                    ->label('Expected Salary')
                    ->money(fn ($record) => $record->salary_currency)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'new',
                        'info' => 'screening',
                        'warning' => ['shortlisted', 'interview'],
                        'success' => ['offer', 'hired'],
                        'danger' => ['rejected', 'withdrawn'],
                    ]),

                Tables\Columns\TextColumn::make('applications_count')
                    ->counts('applications')
                    ->label('Applications'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'new' => 'New Application',
                        'screening' => 'In Screening',
                        'shortlisted' => 'Shortlisted',
                        'interview' => 'Interview Stage',
                        'offer' => 'Offer Stage',
                        'hired' => 'Hired',
                        'rejected' => 'Rejected',
                        'withdrawn' => 'Withdrawn'
                    ]),

                Tables\Filters\Filter::make('salary_range')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('salary_from')
                                ->numeric()
                                ->label('From'),
                            Forms\Components\TextInput::make('salary_to')
                                ->numeric()
                                ->label('To'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['salary_from'],
                                fn (Builder $query, $value): Builder => $query->where('expected_salary', '>=', $value),
                            )
                            ->when(
                                $data['salary_to'],
                                fn (Builder $query, $value): Builder => $query->where('expected_salary', '<=', $value),
                            );
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Applied From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Applied Until'),
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
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download_cv')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn ($record) => $record->resume_path ? Storage::url($record->resume_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Model $record) => $record->resume_path !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('change_status')
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options([
                                    'new' => 'New Application',
                                    'screening' => 'In Screening',
                                    'shortlisted' => 'Shortlisted',
                                    'interview' => 'Interview Stage',
                                    'offer' => 'Offer Stage',
                                    'rejected' => 'Rejected',
                                    'withdrawn' => 'Withdrawn'
                                ])
                                ->required(),
                        ]),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Admin\Resources\CandidateResource\RelationManagers\ApplicationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'view' => Pages\ViewCandidate::route('/{record}'),
            'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }

    private static function getNationalityOptions(): array
    {
        // This would ideally come from a config file or database
        return [
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'IN' => 'India',
            'CN' => 'China',
            'JP' => 'Japan',
            'TZ' => 'Tanzania',
            'KE' => 'Kenya',
            'UG' => 'Uganda',
            'RW' => 'Rwanda',
            'BI' => 'Burundi',
            'SS' => 'South Sudan'
        ];
    }

    private static function getJobTitleOptions(): array
    {
        return [
            'software_engineer' => 'Software Engineer',
            'project_manager' => 'Project Manager',
            'business_analyst' => 'Business Analyst',
            'product_manager' => 'Product Manager',
            'data_scientist' => 'Data Scientist',
            'ux_designer' => 'UX Designer',
            'marketing_manager' => 'Marketing Manager',
            'sales_executive' => 'Sales Executive',
            'hr_manager' => 'HR Manager',
            'financial_analyst' => 'Financial Analyst',
            'operations_manager' => 'Operations Manager',
            'content_writer' => 'Content Writer',
            'graphic_designer' => 'Graphic Designer',
            'system_administrator' => 'System Administrator',
            'qa_engineer' => 'QA Engineer',
            'devops_engineer' => 'DevOps Engineer',
            'network_engineer' => 'Network Engineer',
            'account_manager' => 'Account Manager',
            'executive_assistant' => 'Executive Assistant',
            'customer_service_rep' => 'Customer Service Representative',
            'other' => 'Other'
        ];
    }

    private static function getExperienceRangeOptions(): array
    {
        return [
            '0-1' => 'Less than 1 year',
            '1-2' => '1-2 years',
            '2-3' => '2-3 years',
            '3-5' => '3-5 years',
            '5-7' => '5-7 years',
            '7-10' => '7-10 years',
            '10-15' => '10-15 years',
            '15+' => 'More than 15 years'
        ];
    }

    private static function getCommonSkillsSuggestions(): array
    {
        return [
            'PHP', 'JavaScript', 'Python', 'Java', 'C++', 'C#', 'Ruby', 'Swift',
            'HTML5', 'CSS3', 'React', 'Vue.js', 'Angular', 'Node.js', 'Laravel',
            'MySQL', 'PostgreSQL', 'MongoDB', 'Oracle', 'SQL Server',
            'AWS', 'Azure', 'Google Cloud', 'Docker', 'Kubernetes',
            'Agile', 'Scrum', 'JIRA', 'Trello', 'MS Project',
            'Figma', 'Adobe XD', 'Photoshop', 'Illustrator',
            'Google Analytics', 'Tableau', 'Power BI',
            'SEO', 'SEM', 'Content Marketing', 'Social Media Marketing',
            'Microsoft Office', 'Salesforce', 'Business Analysis', 'Strategic Planning',
            'Leadership', 'Communication', 'Problem Solving', 'Team Management'
        ];
    }

    private static function getLanguageOptions(): array
    {
        return [
            'English',
            'Swahili',
            'Spanish',
            'French',
            'German',
            'Italian',
            'Portuguese',
            'Russian',
            'Chinese (Mandarin)',
            'Japanese',
            'Korean',
            'Arabic',
            'Hindi',
            'Bengali',
            'Dutch',
            'Swedish',
            'Norwegian',
            'Finnish',
            'Danish',
            'Greek',
            'Turkish',
            'Vietnamese',
            'Thai',
            'Indonesian',
            'Malay',
            'Tagalog'
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->full_name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->email,
            'Phone' => $record->phone,
            'Status' => $record->status
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'new')->exists() ? 'warning' : null;
    }
}
