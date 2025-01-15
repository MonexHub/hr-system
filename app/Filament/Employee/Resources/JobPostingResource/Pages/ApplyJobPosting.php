<?php

namespace App\Filament\Employee\Resources\JobPostingResource\Pages;

use App\Filament\Employee\Resources\JobPostingResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Models\JobPosting;

class ApplyJobPosting extends Page
{
    protected static string $resource = JobPostingResource::class;
    protected static string $view = 'filament.employee.resources.job-posting.pages.apply';

    public JobPosting $record;
    public ?array $data = [
        'cover_letter_content' => '',
        'expected_salary' => null,
        'salary_currency' => 'TZS',
        'availability' => null,
        'resume' => null,
        'certificates' => null,
    ];
    public function mount(JobPosting $record): void
    {
        $this->record = $record;

        // Check if already applied
        $employee = auth()->user()->employee;
        if ($employee && $employee->applied_position_id === $record->id) {
            Notification::make()
                ->warning()
                ->title('Already Applied')
                ->body('You have already applied for this position.')
                ->send();

            $this->redirect(route('filament.employee.resources.job-postings.view', ['record' => $record]));
        }

        // Initialize form with default values if needed
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Application Details')
                    ->description('Please provide the following information for your job application')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('expected_salary')
                                    ->label('Expected Salary')
                                    ->required()
                                    ->numeric(),

                                Forms\Components\Select::make('salary_currency')
                                    ->label('Currency')
                                    ->options([
                                        'TZS' => 'Tanzanian Shilling (TZS)',
                                        'USD' => 'US Dollar (USD)',
                                        'EUR' => 'Euro (EUR)',
                                        'GBP' => 'British Pound (GBP)',
                                    ])
                                    ->default('TZS')
                                    ->required(),

                                Forms\Components\Select::make('availability')
                                    ->label('When can you start?')
                                    ->options([
                                        'immediate' => 'Immediately',
                                        'one_week' => 'In one week',
                                        'two_weeks' => 'In two weeks',
                                        'one_month' => 'In one month',
                                        'other' => 'Other (specify in cover letter)',
                                    ])
                                    ->required(),
                            ]),

                        Forms\Components\RichEditor::make('cover_letter_content')
                        ->label('Cover Letter')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'undo',
                                'redo',
                            ])
                            ->helperText('Please explain why you are interested in this position and your relevant qualifications.')
                            ->columnSpanFull(),

                        Forms\Components\Section::make('Required Documents')
                            ->schema([
                                Forms\Components\FileUpload::make('resume')
                                    ->label('CV/Resume')
                                    ->required()
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(5120)
                                    ->helperText('PDF format only, maximum 5MB')
                                    ->downloadable()
                                    ->directory('resumes'),

                                Forms\Components\FileUpload::make('certificates')
                                    ->label('Certificates & Supporting Documents')
                                    ->multiple()
                                    ->reorderable()
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->maxSize(5120)
                                    ->directory('certificates')
                                    ->helperText('PDF or images, maximum 5MB each'),
                            ])
                            ->columns(2),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // Update employee record with application details
        auth()->user()->employee->update([
            'application_status' => 'application_submitted',
            'applied_position_id' => $this->record->id,
            'application_documents' => [
                'resume' => $data['resume'],
                'certificates' => $data['certificates'] ?? [],
                'cover_letter' => $data['cover_letter_content'], // Changed to match the field name
                'salary_expectation' => $data['expected_salary'],
                'salary_currency' => $data['salary_currency'],
                'availability' => $data['availability'],
                'applied_at' => now(),
            ],
        ]);

        Notification::make()
            ->success()
            ->title('Application Submitted')
            ->body('Your application has been submitted successfully.')
            ->send();

        $this->redirect(route('filament.employee.resources.job-postings.view', ['record' => $this->record]));
    }
}
