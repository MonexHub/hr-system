<?php

namespace Database\Seeders;

use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;

class OrganizationUnitSeeder extends Seeder
{
    public function run(): void
    {
        // Create Company (Root Level)
        $company = OrganizationUnit::create([
            'name' => 'SIMBA GROUP',
            'code' => 'SIMBA-HQ',
            'unit_type' => OrganizationUnit::TYPE_COMPANY,
            'description' => 'Main Company Headquarters',
            'is_active' => true,
            'level' => 0,
            'order_index' => 1,
        ]);

        // Create Divisions (Level 1)
        $divisions = [
            [
                'name' => 'Technology Division',
                'code' => 'TECH-DIV',
                'description' => 'Technology and Innovation Division',
            ],
            [
                'name' => 'Operations Division',
                'code' => 'OPS-DIV',
                'description' => 'Operations and Management Division',
            ],
            [
                'name' => 'Business Division',
                'code' => 'BIZ-DIV',
                'description' => 'Business Development Division',
            ],
        ];

        foreach ($divisions as $index => $division) {
            $divisionUnit = OrganizationUnit::create([
                'name' => $division['name'],
                'code' => $division['code'],
                'unit_type' => OrganizationUnit::TYPE_DIVISION,
                'description' => $division['description'],
                'parent_id' => $company->id,
                'is_active' => true,
                'level' => 1,
                'order_index' => $index + 1,
            ]);

            // Create Departments for each Division (Level 2)
            $this->createDepartments($divisionUnit);
        }
    }

    private function createDepartments(OrganizationUnit $division): void
    {
        $departments = [
            'Technology Division' => [
                ['name' => 'Software Development', 'code' => 'TECH-SD'],
                ['name' => 'Infrastructure', 'code' => 'TECH-INF'],
                ['name' => 'Digital Innovation', 'code' => 'TECH-DI'],
            ],
            'Operations Division' => [
                ['name' => 'Human Resources', 'code' => 'OPS-HR'],
                ['name' => 'Finance', 'code' => 'OPS-FIN'],
                ['name' => 'Administration', 'code' => 'OPS-ADM'],
            ],
            'Business Division' => [
                ['name' => 'Sales', 'code' => 'BIZ-SALES'],
                ['name' => 'Marketing', 'code' => 'BIZ-MKT'],
                ['name' => 'Customer Relations', 'code' => 'BIZ-CR'],
            ],
        ];

        $departmentList = $departments[$division->name] ?? [];

        foreach ($departmentList as $index => $dept) {
            $department = OrganizationUnit::create([
                'name' => $dept['name'],
                'code' => $dept['code'],
                'unit_type' => OrganizationUnit::TYPE_DEPARTMENT,
                'description' => "Department of {$dept['name']}",
                'parent_id' => $division->id,
                'is_active' => true,
                'level' => 2,
                'order_index' => $index + 1,
                'annual_budget' => rand(100000, 500000),
                'max_headcount' => rand(10, 50),
                'current_headcount' => 0,
            ]);

            // Create Teams for each Department (Level 3)
            $this->createTeams($department);
        }
    }

    private function createTeams(OrganizationUnit $department): void
    {
        $teams = [
            'Software Development' => [
                ['name' => 'Frontend Team', 'code' => 'SD-FE'],
                ['name' => 'Backend Team', 'code' => 'SD-BE'],
                ['name' => 'QA Team', 'code' => 'SD-QA'],
            ],
            'Infrastructure' => [
                ['name' => 'Cloud Team', 'code' => 'INF-CLD'],
                ['name' => 'Network Team', 'code' => 'INF-NET'],
                ['name' => 'Security Team', 'code' => 'INF-SEC'],
            ],
            'Human Resources' => [
                ['name' => 'Recruitment Team', 'code' => 'HR-REC'],
                ['name' => 'Training Team', 'code' => 'HR-TRN'],
                ['name' => 'Employee Relations', 'code' => 'HR-ER'],
            ],
            // Add more teams as needed
        ];

        $teamList = $teams[$department->name] ?? [];

        foreach ($teamList as $index => $team) {
            OrganizationUnit::create([
                'name' => $team['name'],
                'code' => $team['code'],
                'unit_type' => OrganizationUnit::TYPE_TEAM,
                'description' => "{$team['name']} under {$department->name}",
                'parent_id' => $department->id,
                'is_active' => true,
                'level' => 3,
                'order_index' => $index + 1,
                'max_headcount' => rand(5, 15),
                'current_headcount' => 0,
            ]);
        }
    }
}
