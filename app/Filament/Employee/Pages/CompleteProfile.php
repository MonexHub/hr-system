<?php

namespace App\Filament\Employee\Pages;

use App\Models\Employee;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Filament\Pages\Page;
use Filament\Forms\Components\Wizard;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Components\FileUpload;

class CompleteProfile extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $title = 'Complete Your Profile';
    protected static string $view = 'filament.employee.pages.complete-profile';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->authorizeAccess();

        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            $employee = $this->createInitialEmployeeRecord($user);
        }

        $this->form->fill([
            'personal_information' => [
                'email' => $user->email,
                'first_name' => $employee->first_name ?? $user->name,
                'last_name' => $employee->last_name ?? '',
                'birthdate' => $employee->birthdate,
                'gender' => $employee->gender,
                'profile_photo' => $employee->profile_photo,
            ],
            'contact_details' => [
                'phone' => $employee->phone,
                'address' => $employee->address,
                'city' => $employee->city,
                'state' => $employee->state,
                'postal_code' => $employee->postal_code,
            ],
            'emergency_contact' => [
                'emergency_contact_name' => $employee->emergency_contact_name,
                'emergency_contact_relationship' => $employee->emergency_contact_relationship,
                'emergency_contact_phone' => $employee->emergency_contact_phone,
            ],
            'documents' => [
                'id_proof' => $employee->id_proof,
                'resume' => $employee->resume,
            ],
        ]);
    }

    protected function authorizeAccess(): void
    {
        $employee = auth()->user()->employee;

        if ($employee && $employee->application_status !== 'profile_incomplete') {
            $this->redirect('/employee');
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Personal Information')
                        ->description('Basic personal details')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('personal_information.first_name')
                                        ->label('First Name')
                                        ->required()
                                        ->maxLength(255)
                                        ->autocomplete('given-name'),

                                    Forms\Components\TextInput::make('personal_information.last_name')
                                        ->label('Last Name')
                                        ->required()
                                        ->maxLength(255)
                                        ->autocomplete('family-name'),

                                    Forms\Components\TextInput::make('personal_information.email')
                                        ->label('Email')
                                        ->email()
                                        ->required()
                                        ->disabled()
                                        ->autocomplete('email'),

                                    Forms\Components\DatePicker::make('personal_information.birthdate')
                                        ->label('Date of Birth')
                                        ->required()
                                        ->maxDate(now()->subYears(18))
                                        ->displayFormat('d/m/Y'),

                                    Forms\Components\Select::make('personal_information.gender')
                                        ->label('Gender')
                                        ->options([
                                            'male' => 'Male',
                                            'female' => 'Female',
                                            'other' => 'Other',
                                        ])
                                        ->required(),

                                    FileUpload::make('personal_information.profile_photo')
                                        ->label('Profile Photo')
                                        ->image()
                                        ->imageEditor()
                                        ->circleCropper()
                                        ->directory('profile-photos'),
                                ]),
                        ]),

                    Wizard\Step::make('Contact Details')
                        ->description('Your contact information')
                        ->icon('heroicon-o-phone')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('contact_details.phone')
                                        ->label('Phone Number')
                                        ->tel()
                                        ->required()
                                        ->mask('(999) 999-9999')
                                        ->placeholder('(555) 000-0000'),

                                    Forms\Components\TextInput::make('contact_details.address')
                                        ->label('Street Address')
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('contact_details.city')
                                        ->label('City')
                                        ->required(),

                                    Forms\Components\TextInput::make('contact_details.state')
                                        ->label('State/Province')
                                        ->required(),

                                    Forms\Components\TextInput::make('contact_details.postal_code')
                                        ->label('Postal Code')
                                        ->required(),
                                ]),
                        ]),

                    Wizard\Step::make('Emergency Contact')
                        ->description('Emergency contact information')
                        ->icon('heroicon-o-heart')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('emergency_contact.emergency_contact_name')
                                        ->label('Contact Name')
                                        ->required(),

                                    Forms\Components\Select::make('emergency_contact.emergency_contact_relationship')
                                        ->label('Relationship')
                                        ->options([
                                            'spouse' => 'Spouse',
                                            'parent' => 'Parent',
                                            'child' => 'Child',
                                            'sibling' => 'Sibling',
                                            'friend' => 'Friend',
                                            'other' => 'Other',
                                        ])
                                        ->required(),

                                    Forms\Components\TextInput::make('emergency_contact.emergency_contact_phone')
                                        ->label('Contact Phone')
                                        ->tel()
                                        ->required()
                                        ->mask('(999) 999-9999'),
                                ]),
                        ]),

                    Wizard\Step::make('Documents')
                        ->description('Upload required documents')
                        ->icon('heroicon-o-document')
                        ->schema([
                            Forms\Components\Grid::make(1)
                                ->schema([
                                    FileUpload::make('documents.id_proof')
                                        ->label('ID Proof')
                                        ->required()
                                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                                        ->directory('id-proofs')
                                        ->maxSize(5120)
                                        ->helperText('Upload a valid ID proof (PDF or Image, max 5MB)'),

                                    FileUpload::make('documents.resume')
                                        ->label('Resume/CV')
                                        ->required()
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->directory('resumes')
                                        ->maxSize(5120)
                                        ->helperText('Upload your resume in PDF format (max 5MB)'),
                                ]),
                        ]),
                ])
                    ->persistStepInQueryString()
                    ->skippable()
                    ->submitAction(view('filament.employee.pages.complete-profile-submit-button')),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        try {
            $data = $this->form->getState();

            $user = auth()->user();

            $employee = $user->employee;
            if (!$employee) {
                $employee = $this->createInitialEmployeeRecord($user);
            }

            // Flatten the nested data structure
            $updateData = array_merge(
                $data['personal_information'] ?? [],
                $data['contact_details'] ?? [],
                $data['emergency_contact'] ?? [],
                ['application_status' => 'active']
            );

            // Handle document uploads
            if (isset($data['documents'])) {
                $updateData = array_merge($updateData, $data['documents']);
            }

            $employee->update($updateData);

            Notification::make()
                ->title('Profile Completed')
                ->body('Your profile has been successfully updated.')
                ->success()
                ->send();

            // Log successful profile completion
            Log::info('Employee profile completed', [
                'employee_id' => $employee->id,
                'user_id' => auth()->id(),
            ]);

            $this->redirect('/employee');
        } catch (ValidationException $e) {
            Log::warning('Profile validation failed', [
                'user_id' => auth()->id(),
                'errors' => $e->errors(),
            ]);

            Notification::make()
                ->title('Validation Error')
                ->body('Please check the form for errors.')
                ->danger()
                ->send();

            throw $e;
        } catch (\Exception $e) {
            Log::error('Profile completion error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            Notification::make()
                ->title('Error Updating Profile')
                ->body('Unable to update your profile. Please try again or contact support.')
                ->danger()
                ->send();

            throw $e;
        }
    }

    protected function createInitialEmployeeRecord($user): Employee
    {
        $nameParts = explode(' ', $user->name, 2);

        return $user->employee()->create([
            'user_id' => $user->id,
            'employee_code' => $this->generateEmployeeCode($user->id),
            'first_name' => $nameParts[0] ?? $user->name,
            'last_name' => $nameParts[1] ?? '',
            'application_status' => 'profile_incomplete',
            'birthdate' => now()->subYears(18),
            'gender' => 'other',
            'contract_type' => 'undefined',
            'appointment_date' => now(),
            'job_title' => 'unassigned',
            'branch' => 'unassigned',
            'salary' => 0,
            'employment_status' => 'pending',
        ]);
    }

    protected function generateEmployeeCode(int $userId): string
    {
        return 'EMP-' . str_pad($userId, 5, '0', STR_PAD_LEFT);
    }

    public function getTitle(): string
    {
        return static::$title ?? 'Complete Your Profile';
    }

    public function getSubheading(): string
    {
        return 'Complete your profile information to get started';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function hasNavigationItems(): bool
    {
        return false;
    }

    public function getCachedSubNavigation(): array
    {
        return [];
    }

    public function getSubNavigation(): array
    {
        return [];
    }
}
