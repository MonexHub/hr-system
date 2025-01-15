<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            // Core Departments
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Manages recruitment, employee relations, training, and development',
                'location' => 'Head Office - Floor 3',
                'email' => 'hr@company.co.tz',
                'phone' => '+255 700 000 001',
                'annual_budget' => 150000000, // TSh
                'max_headcount' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Information Technology',
                'code' => 'IT',
                'description' => 'Manages IT infrastructure, software development, and technical support',
                'location' => 'Head Office - Floor 2',
                'email' => 'it@company.co.tz',
                'phone' => '+255 700 000 002',
                'annual_budget' => 200000000,
                'max_headcount' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Finance',
                'code' => 'FIN',
                'description' => 'Manages company finances, accounting, and financial planning',
                'location' => 'Head Office - Floor 4',
                'email' => 'finance@company.co.tz',
                'phone' => '+255 700 000 003',
                'annual_budget' => 180000000,
                'max_headcount' => 12,
                'is_active' => true,
            ],

            // Business Operations
            [
                'name' => 'Sales & Marketing',
                'code' => 'SAM',
                'description' => 'Handles sales operations, marketing strategies, and customer relationships',
                'location' => 'Head Office - Floor 1',
                'email' => 'sales@company.co.tz',
                'phone' => '+255 700 000 004',
                'annual_budget' => 250000000,
                'max_headcount' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Operations',
                'code' => 'OPS',
                'description' => 'Manages day-to-day business operations and logistics',
                'location' => 'Operations Center',
                'email' => 'operations@company.co.tz',
                'phone' => '+255 700 000 005',
                'annual_budget' => 300000000,
                'max_headcount' => 25,
                'is_active' => true,
            ],

            // Support Departments
            [
                'name' => 'Customer Service',
                'code' => 'CS',
                'description' => 'Provides customer support and handles customer inquiries',
                'location' => 'Customer Service Center',
                'email' => 'support@company.co.tz',
                'phone' => '+255 700 000 006',
                'annual_budget' => 120000000,
                'max_headcount' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Legal',
                'code' => 'LEG',
                'description' => 'Handles legal affairs and compliance',
                'location' => 'Head Office - Floor 4',
                'email' => 'legal@company.co.tz',
                'phone' => '+255 700 000 007',
                'annual_budget' => 100000000,
                'max_headcount' => 5,
                'is_active' => true,
            ],

            // Product Development
            [
                'name' => 'Research & Development',
                'code' => 'RND',
                'description' => 'Focuses on product innovation and development',
                'location' => 'R&D Center',
                'email' => 'research@company.co.tz',
                'phone' => '+255 700 000 008',
                'annual_budget' => 200000000,
                'max_headcount' => 15,
                'is_active' => true,
            ],

            // Sub-departments
            [
                'name' => 'Technical Support',
                'code' => 'ITS',
                'description' => 'Provides technical support and maintenance',
                'location' => 'Head Office - Floor 2',
                'email' => 'support.it@company.co.tz',
                'phone' => '+255 700 000 009',
                'annual_budget' => 80000000,
                'max_headcount' => 8,
                'is_active' => true,
                'parent_id' => 2, // IT Department
            ],
            [
                'name' => 'Recruitment',
                'code' => 'HRR',
                'description' => 'Handles recruitment and talent acquisition',
                'location' => 'Head Office - Floor 3',
                'email' => 'recruitment@company.co.tz',
                'phone' => '+255 700 000 010',
                'annual_budget' => 50000000,
                'max_headcount' => 5,
                'is_active' => true,
                'parent_id' => 1, // HR Department
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        // Update parent relationships for sub-departments
        $this->command->info('Setting up department hierarchies...');

        // Set Technical Support under IT
        Department::where('code', 'ITS')->update(['parent_id' => Department::where('code', 'IT')->first()->id]);

        // Set Recruitment under HR
        Department::where('code', 'HRR')->update(['parent_id' => Department::where('code', 'HR')->first()->id]);

        $this->command->info('Department seeding completed!');
    }
}
