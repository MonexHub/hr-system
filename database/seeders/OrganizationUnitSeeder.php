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
                'name' => 'Logistics Division',
                'code' => 'LOG-DIV',
                'description' => 'Logistics and Transportation Division',
            ],
            [
                'name' => 'Operations Division',
                'code' => 'OPS-DIV',
                'description' => 'Operations and Services Division',
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
            'Logistics Division' => [
                ['name' => 'SIMBA LOGISTIC', 'code' => 'LOG-SL'],
                ['name' => 'SIMBA COURIER', 'code' => 'LOG-SC'],
                ['name' => 'SIMBA VIT', 'code' => 'LOG-SV'],
            ],
            'Operations Division' => [
                ['name' => 'PUMP', 'code' => 'OPS-PUMP'],
                ['name' => 'Human Resources', 'code' => 'OPS-HR'],
                ['name' => 'Finance', 'code' => 'OPS-FIN'],
                ['name' => 'SHEQ', 'code' => 'OPS-SHEQ'],
            ],
            'Business Division' => [
                ['name' => 'SIMBA FOODS', 'code' => 'BIZ-SF'],
                ['name' => 'SIMBA MONEY', 'code' => 'BIZ-SM'],
                ['name' => 'Marketing', 'code' => 'BIZ-MKT'],
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
                'annual_budget' => rand(1000000, 5000000),
                'max_headcount' => rand(20, 100),
                'current_headcount' => 0,
            ]);

            // Create Teams for each Department (Level 3)
            $this->createTeams($department);
        }
    }

    private function createTeams(OrganizationUnit $department): void
    {
        $teams = [
            'SIMBA LOGISTIC' => [
                ['name' => 'Fleet Management', 'code' => 'SL-FM'],
                ['name' => 'Tracking Administration', 'code' => 'SL-TA'],
                ['name' => 'Distribution', 'code' => 'SL-DIST'],
            ],
            'SIMBA COURIER' => [
                ['name' => 'Fleet Operations', 'code' => 'SC-FO'],
                ['name' => 'Customer Service', 'code' => 'SC-CS'],
                ['name' => 'Warehouse', 'code' => 'SC-WH'],
                ['name' => 'Key Accounts', 'code' => 'SC-KA'],
            ],
            'SIMBA VIT' => [
                ['name' => 'Cash Processing', 'code' => 'SV-CP'],
                ['name' => 'Car Operations', 'code' => 'SV-CO'],
                ['name' => 'Control Room', 'code' => 'SV-CR'],
            ],
            'PUMP' => [
                ['name' => 'Technical Support', 'code' => 'PUMP-TS'],
                ['name' => 'Maintenance', 'code' => 'PUMP-MNT'],
                ['name' => 'Sales', 'code' => 'PUMP-SLS'],
            ],
            'SIMBA FOODS' => [
                ['name' => 'Sales and Marketing', 'code' => 'SF-SM'],
                ['name' => 'Operations', 'code' => 'SF-OPS'],
                ['name' => 'Inventory', 'code' => 'SF-INV'],
            ],
            'SIMBA MONEY' => [
                ['name' => 'IT Operations', 'code' => 'SM-IT'],
                ['name' => 'Business Development', 'code' => 'SM-BD'],
            ],
            'Human Resources' => [
                ['name' => 'Recruitment', 'code' => 'HR-REC'],
                ['name' => 'Training', 'code' => 'HR-TRN'],
                ['name' => 'Employee Relations', 'code' => 'HR-ER'],
            ],
            'Finance' => [
                ['name' => 'Accounting', 'code' => 'FIN-ACC'],
                ['name' => 'Payroll', 'code' => 'FIN-PAY'],
                ['name' => 'Budget', 'code' => 'FIN-BDG'],
            ],
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
                'max_headcount' => rand(5, 25),
                'current_headcount' => 0,
            ]);
        }
    }
}
