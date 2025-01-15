<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\JobPosting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobPostingSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have an admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Fetch departments
        $departments = Department::all()->keyBy('code');

        $jobPostings = [
            [
                'department_id' => $departments['HR']->id,
                'title' => 'HR Manager',
                'description' => 'We are looking for an experienced HR Manager to lead our human resources initiatives and support our growing organization.',
                'requirements' => json_encode([
                    ['requirement' => '7+ years of HR experience'],
                    ['requirement' => 'Strong knowledge of Tanzania labor laws'],
                    ['requirement' => 'Experience with HRIS systems'],
                    ['requirement' => 'Strategic HR planning expertise'],
                ]),
                'responsibilities' => json_encode([
                    ['responsibility' => 'Develop and implement HR policies'],
                    ['responsibility' => 'Manage recruitment and onboarding processes'],
                    ['responsibility' => 'Handle employee relations'],
                    ['responsibility' => 'Design employee development programs'],
                ]),
                'employment_type' => 'full_time',
                'location' => 'Dar es Salaam',
                'is_remote' => false,
                'salary_min' => 4000000,
                'salary_max' => 6000000,
                'salary_currency' => 'TZS',
                'positions_available' => 1,
                'status' => 'published',
                'skills_required' => json_encode(['HR Management', 'Recruitment', 'Employee Relations', 'HR Strategy']),
                'minimum_years_experience' => 7,
                'education_level' => 'Bachelor\'s Degree in HR or related field',
                'benefits' => json_encode([
                    ['benefit' => 'Competitive package'],
                    ['benefit' => 'Medical insurance'],
                    ['benefit' => 'Professional development support'],
                ]),
                'created_by' => $adminUser->id,
            ],
            // ... other job postings with explicit created_by
        ];

        // Process each job posting
        foreach ($jobPostings as $postingData) {
            // Ensure created_by is set
            if (!isset($postingData['created_by'])) {
                $postingData['created_by'] = $adminUser->id;
            }

            // Generate unique position code
            $postingData['position_code'] = $this->generateUniquePositionCode();
            $postingData['publishing_date'] = now();
            $postingData['closing_date'] = now()->addDays(30);
            $postingData['created_at'] = now();
            $postingData['updated_at'] = now();

            try {
                // Use DB::table to bypass model events and ensure all fields are set
                DB::table('job_postings')->insert($postingData);
            } catch (\Exception $e) {
                \Log::error("Error creating job posting: " . $e->getMessage());
                $this->command->error("Error creating job posting {$postingData['title']}: " . $e->getMessage());
            }
        }

        $this->command->info(count($jobPostings) . ' job postings seeded successfully!');
    }

    /**
     * Generate a unique position code
     *
     * @return string
     */
    protected function generateUniquePositionCode(): string
    {
        // Ensure the code is unique
        do {
            $code = 'JOB-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (DB::table('job_postings')->where('position_code', $code)->exists());

        return $code;
    }
}
