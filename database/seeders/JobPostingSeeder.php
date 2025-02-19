<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class JobPostingSeeder extends Seeder
{
    protected array $jobTitles = [
        'Software Engineer',
        'Sales Manager',
        'Accountant',
        'Marketing Officer',
        'HR Officer',
        'Financial Analyst',
        'Project Coordinator',
        'Customer Service Representative',
        'Operations Manager',
        'Administrative Assistant',
        'Branch Manager',
        'Business Development Officer',
        'ICT Officer',
        'Logistics Coordinator',
        'Research Assistant'
    ];

    protected array $requirements = [
        'Bachelor\'s degree in related field',
        'Strong communication skills in English and Swahili',
        'Problem-solving abilities',
        'Team player mentality',
        'Attention to detail',
        'Valid TIN number',
        'NSSF registration',
        'Proficiency in MS Office',
        'Excellent interpersonal skills'
    ];

    protected array $responsibilities = [
        'Develop and implement departmental strategies',
        'Collaborate with various teams and stakeholders',
        'Prepare and maintain documentation',
        'Participate in team meetings and training',
        'Monitor and report on project progress',
        'Ensure compliance with local regulations',
        'Coordinate with government agencies when needed',
        'Maintain professional relationships with stakeholders'
    ];

    protected array $skills = [
        'Computer Literacy',
        'Report Writing',
        'Project Management',
        'Team Leadership',
        'Communication',
        'Problem Solving',
        'English Proficiency',
        'Swahili Proficiency',
        'Data Analysis',
        'Strategic Planning'
    ];

    protected array $benefits = [
        'NSSF',
        'Health insurance',
        'Transport allowance',
        'House allowance',
        'Annual leave',
        'Sick leave',
        'Professional development opportunities',
        'Performance bonus',
        'Mobile allowance',
        'Meal allowance'
    ];

    protected array $educationLevels = [
        'Certificate',
        'Diploma',
        'Advanced Diploma',
        'Bachelor\'s Degree',
        'Master\'s Degree',
        'PhD'
    ];

    protected array $screeningQuestions = [
        'How many years of experience do you have in this field?',
        'Do you have a valid TIN number?',
        'Are you registered with NSSF?',
        'What is your expected salary range?',
        'When can you start?',
        'Are you willing to relocate if necessary?',
        'Are you fluent in both English and Swahili?',
        'Do you have experience working in Tanzania?'
    ];

    public function run(): void
    {
        // Get some random departments and users for relationships
        $departments = Department::all();
        $users = User::all();

        if ($departments->isEmpty() || $users->isEmpty()) {
            $this->command->error('Please seed departments and users first!');
            return;
        }

        // Create 50 job postings
        for ($i = 0; $i < 50; $i++) {
            $publishingDate = Carbon::now()->subDays(rand(0, 30));
            $closingDate = $publishingDate->copy()->addDays(rand(15, 45));

            // Salary in TZS (Tanzanian Shilling)
            $salaryMin = rand(800000, 2000000); // Starting from 800,000 TZS
            $salaryMax = $salaryMin + rand(300000, 1000000);

            $positionsAvailable = rand(1, 5);
            $positionsFilled = rand(0, $positionsAvailable);

            $status = $this->getRandomStatus($publishingDate, $closingDate, $positionsAvailable, $positionsFilled);

            $creator = $users->random();
            $approver = $users->random();

            JobPosting::create([
                'department_id' => $departments->random()->id,
                'title' => $this->jobTitles[array_rand($this->jobTitles)],
                'description' => $this->generateDescription(),
                'requirements' => $this->getRandomArrayElements($this->requirements),
                'responsibilities' => $this->getRandomArrayElements($this->responsibilities),
                'employment_type' => $this->getRandomEmploymentType(),
                'location' => $this->getRandomLocation(),
                'is_remote' => (bool)rand(0, 1),
                'salary_min' => $salaryMin,
                'salary_max' => $salaryMax,
                'salary_currency' => 'TZS',
                'hide_salary' => (bool)rand(0, 1),
                'positions_available' => $positionsAvailable,
                'positions_filled' => $positionsFilled,
                'publishing_date' => $publishingDate,
                'closing_date' => $closingDate,
                'status' => $status,
                'is_featured' => (bool)rand(0, 1),
                'skills_required' => $this->getRandomArrayElements($this->skills),
                'education_requirements' => $this->getRandomArrayElements($this->educationLevels, 1),
                'experience_requirements' => ['years' => rand(1, 10), 'level' => 'Professional'],
                'benefits' => $this->getRandomArrayElements($this->benefits),
                'screening_questions' => $this->getRandomArrayElements($this->screeningQuestions),
                'minimum_years_experience' => rand(1, 5),
                'education_level' => $this->educationLevels[array_rand($this->educationLevels)],
                'created_by' => $creator->id,
                'approved_by' => $status === 'published' ? $approver->id : null,
                'approved_at' => $status === 'published' ? $publishingDate : null,
            ]);
        }

        $this->command->info('Job postings seeded successfully!');
    }

    protected function generateDescription(): string
    {
        return "We are seeking a motivated professional to join our growing team in Tanzania. " .
            "This role offers excellent opportunities for career development and growth " .
            "in a dynamic work environment. The ideal candidate will be results-oriented, " .
            "with strong communication skills in both English and Swahili, and ready to " .
            "contribute to our organization's success in the East African market.";
    }

    protected function getRandomArrayElements(array $array, int $count = null): array
    {
        $count = $count ?? rand(2, count($array));
        shuffle($array);
        return array_slice($array, 0, min($count, count($array)));
    }

    protected function getRandomEmploymentType(): string
    {
        $types = [
            'permanent',
            'contract',
            'temporary',
            'internship',
            'probation'
        ];
        return $types[array_rand($types)];
    }

    protected function getRandomLocation(): string
    {
        $locations = [
            'Dar es Salaam',
            'Dodoma',
            'Arusha',
            'Mwanza',
            'Zanzibar',
            'Mbeya',
            'Tanga',
            'Morogoro',
            'Tabora',
            'Kigoma',
            'Mtwara',
            'Iringa'
        ];
        return $locations[array_rand($locations)] . ', Tanzania';
    }

    protected function getRandomStatus(
        Carbon $publishingDate,
        Carbon $closingDate,
        int $positionsAvailable,
        int $positionsFilled
    ): string {
        $now = Carbon::now();

        if ($positionsFilled >= $positionsAvailable) {
            return 'filled';
        }

        if ($closingDate->isPast()) {
            return 'closed';
        }

        if ($publishingDate->isFuture()) {
            return 'pending_approval';
        }

        $statuses = ['draft', 'pending_approval', 'published'];
        return $statuses[array_rand($statuses)];
    }
}
