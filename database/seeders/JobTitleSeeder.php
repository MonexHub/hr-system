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
                'name' => 'Group CEO',
                'description' => 'Chief Executive Officer of Simba Group, responsible for overall group strategy, vision, and leadership',
                'net_salary_min' => 10000000,
                'net_salary_max' => 15000000,
                'is_active' => true,
            ],
            [
                'name' => 'Country Manager',
                'description' => 'Overall responsibility for country operations',
                'net_salary_min' => 1200000,
                'net_salary_max' => 2000000,
                'is_active' => true,
            ],
            [
                'name' => 'Head of Operations',
                'description' => 'Leads operations across different business units',
                'net_salary_min' => 3500000,
                'net_salary_max' => 5000000,
                'is_active' => true,
            ],
            [
                'name' => 'Head of Customer Experience',
                'description' => 'Leads customer service and experience initiatives',
                'net_salary_min' => 2500000,
                'net_salary_max' => 3500000,
                'is_active' => true,
            ],
            [
                'name' => 'Head of Key Account Personnel',
                'description' => 'Manages key account relationships and team',
                'net_salary_min' => 800000,
                'net_salary_max' => 1000000,
                'is_active' => true,
            ],

            // Managerial Positions
            [
                'name' => 'Cash Processing Centre Manager',
                'description' => 'Manages cash processing operations',
                'net_salary_min' => 2000000,
                'net_salary_max' => 3000000,
                'is_active' => true,
            ],
            [
                'name' => 'Fleet Manager',
                'description' => 'Manages fleet operations and logistics',
                'net_salary_min' => 1800000,
                'net_salary_max' => 2500000,
                'is_active' => true,
            ],
            [
                'name' => 'Marketing Manager',
                'description' => 'Leads marketing strategies and initiatives',
                'net_salary_min' => 900000,
                'net_salary_max' => 1200000,
                'is_active' => true,
            ],
            [
                'name' => 'Technical Support Manager',
                'description' => 'Manages technical support operations',
                'net_salary_min' => 1800000,
                'net_salary_max' => 2500000,
                'is_active' => true,
            ],

            // Supervisor Positions
            [
                'name' => 'Fleet Supervisor',
                'description' => 'Supervises fleet operations',
                'net_salary_min' => 800000,
                'net_salary_max' => 1100000,
                'is_active' => true,
            ],
            [
                'name' => 'Quality Assurance Supervisor',
                'description' => 'Oversees quality assurance processes',
                'net_salary_min' => 1000000,
                'net_salary_max' => 1500000,
                'is_active' => true,
            ],
            [
                'name' => 'Supervisor Fleet Officer',
                'description' => 'Supervises fleet officers and operations',
                'net_salary_min' => 2500000,
                'net_salary_max' => 3500000,
                'is_active' => true,
            ],

            // Professional Positions
            [
                'name' => 'Accountant',
                'description' => 'Handles accounting and financial matters',
                'net_salary_min' => 600000,
                'net_salary_max' => 1200000,
                'is_active' => true,
            ],
            [
                'name' => 'Fleet Officer',
                'description' => 'Manages fleet-related operations',
                'net_salary_min' => 500000,
                'net_salary_max' => 700000,
                'is_active' => true,
            ],
            [
                'name' => 'Key Account Personnel',
                'description' => 'Manages key client relationships',
                'net_salary_min' => 700000,
                'net_salary_max' => 1400000,
                'is_active' => true,
            ],
            [
                'name' => 'Customer Service',
                'description' => 'Provides customer support and service',
                'net_salary_min' => 450000,
                'net_salary_max' => 800000,
                'is_active' => true,
            ],
            [
                'name' => 'Warehouse Officer',
                'description' => 'Manages warehouse operations',
                'net_salary_min' => 500000,
                'net_salary_max' => 1000000,
                'is_active' => true,
            ],
            [
                'name' => 'Quality Assurance Officer',
                'description' => 'Ensures quality standards are met',
                'net_salary_min' => 700000,
                'net_salary_max' => 900000,
                'is_active' => true,
            ],
            [
                'name' => 'Pump Technician',
                'description' => 'Maintains and repairs pump equipment',
                'net_salary_min' => 500000,
                'net_salary_max' => 1000000,
                'is_active' => true,
            ],

            // Operational Positions
            [
                'name' => 'Driver/Rider',
                'description' => 'Operates delivery vehicles',
                'net_salary_min' => 390000,
                'net_salary_max' => 520000,
                'is_active' => true,
            ],
            [
                'name' => 'Car Commander',
                'description' => 'Leads vehicle operations team',
                'net_salary_min' => 600000,
                'net_salary_max' => 800000,
                'is_active' => true,
            ],
            [
                'name' => 'Cash Processor',
                'description' => 'Handles cash processing operations',
                'net_salary_min' => 500000,
                'net_salary_max' => 800000,
                'is_active' => true,
            ],
            [
                'name' => 'Porter',
                'description' => 'Handles loading and unloading operations',
                'net_salary_min' => 350000,
                'net_salary_max' => 450000,
                'is_active' => true,
            ],

            // Support Positions
            [
                'name' => 'Janitor',
                'description' => 'Maintains cleanliness of facilities',
                'net_salary_min' => 330000,
                'net_salary_max' => 400000,
                'is_active' => true,
            ],
            [
                'name' => 'Warehouse Support',
                'description' => 'Assists in warehouse operations',
                'net_salary_min' => 270000,
                'net_salary_max' => 350000,
                'is_active' => true,
            ],
            [
                'name' => 'Steward',
                'description' => 'Provides facility support services',
                'net_salary_min' => 600000,
                'net_salary_max' => 700000,
                'is_active' => true,
            ],

            // IT Positions
            [
                'name' => 'CTO',
                'description' => 'Leads technology strategy and operations',
                'net_salary_min' => 1200000,
                'net_salary_max' => 2000000,
                'is_active' => true,
            ],
            [
                'name' => 'IT System Administrator',
                'description' => 'Manages IT systems and infrastructure',
                'net_salary_min' => 1200000,
                'net_salary_max' => 1800000,
                'is_active' => true,
            ],
            [
                'name' => 'IT Officer',
                'description' => 'Provides IT support and maintenance',
                'net_salary_min' => 500000,
                'net_salary_max' => 800000,
                'is_active' => true,
            ],

            // Specialized Positions
            [
                'name' => 'SHEQ Manager',
                'description' => 'Manages safety, health, environment and quality',
                'net_salary_min' => 2500000,
                'net_salary_max' => 3500000,
                'is_active' => true,
            ],
            [
                'name' => 'Group HR Director',
                'description' => 'Leads group-wide HR strategy and operations',
                'net_salary_min' => 1200000,
                'net_salary_max' => 2000000,
                'is_active' => true,
            ],
            [
                'name' => 'Brand Manager',
                'description' => 'Manages brand strategy and development',
                'net_salary_min' => 2000000,
                'net_salary_max' => 3000000,
                'is_active' => true,
            ],
        ];

        foreach ($jobTitles as $jobTitle) {
            JobTitle::create($jobTitle);
        }
    }
}
