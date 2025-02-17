<?php

namespace Database\Seeders;

use App\Models\JobTitle;
use Illuminate\Database\Seeder;

class JobTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobTitles = [
            // Executive Positions

            [
                'name' => 'Chief Executive Officer',
                'description' => 'Responsible for overall company strategy and leadership',
                'net_salary_min' => 15000000,
                'net_salary_max' => 25000000,
                'is_active' => true,
            ],

            // Head of Departments
            [
                'name' => 'Head of Human Resources',
                'description' => 'Leads the HR department and oversees all HR operations',
                'net_salary_min' => 8000000,
                'net_salary_max' => 12000000,
                'is_active' => true,
            ],
            [
                'name' => 'Head of Finance',
                'description' => 'Leads the Finance department and oversees financial operations',
                'net_salary_min' => 8000000,
                'net_salary_max' => 12000000,
                'is_active' => true,
            ],
            [
                'name' => 'Head of IT',
                'description' => 'Leads the IT department and oversees technology strategy',
                'net_salary_min' => 8000000,
                'net_salary_max' => 12000000,
                'is_active' => true,
            ],
            [
                'name' => 'Head of Operations',
                'description' => 'Leads operations and oversees day-to-day activities',
                'net_salary_min' => 8000000,
                'net_salary_max' => 12000000,
                'is_active' => true,
            ],

            // Managerial Positions
            [
                'name' => 'Human Resources Manager',
                'description' => 'Manages HR operations and employee relations',
                'net_salary_min' => 5000000,
                'net_salary_max' => 8000000,
                'is_active' => true,
            ],
            [
                'name' => 'Finance Manager',
                'description' => 'Manages financial planning and accounting operations',
                'net_salary_min' => 5000000,
                'net_salary_max' => 8000000,
                'is_active' => true,
            ],
            [
                'name' => 'Project Manager',
                'description' => 'Manages project planning, execution, and delivery',
                'net_salary_min' => 4000000,
                'net_salary_max' => 7000000,
                'is_active' => true,
            ],

            // Professional Positions
            [
                'name' => 'Software Engineer',
                'description' => 'Develops and maintains software applications',
                'net_salary_min' => 3000000,
                'net_salary_max' => 6000000,
                'is_active' => true,
            ],
            [
                'name' => 'Accountant',
                'description' => 'Handles accounting and financial reporting',
                'net_salary_min' => 2500000,
                'net_salary_max' => 4500000,
                'is_active' => true,
            ],
            [
                'name' => 'HR Officer',
                'description' => 'Handles HR administrative tasks and employee support',
                'net_salary_min' => 2000000,
                'net_salary_max' => 3500000,
                'is_active' => true,
            ],

            // Support Positions
            [
                'name' => 'Administrative Assistant',
                'description' => 'Provides administrative support to office operations',
                'net_salary_min' => 1500000,
                'net_salary_max' => 2500000,
                'is_active' => true,
            ],
        ];

        foreach ($jobTitles as $jobTitle) {
            JobTitle::create($jobTitle);
        }
    }
}
