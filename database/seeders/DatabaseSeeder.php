<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. First, set up roles and permissions
            ShieldSeeder::class,

            // 2. Organization structure (needed for users and employees)
            OrganizationUnitSeeder::class,
            DepartmentSeeder::class,
            JobTitleSeeder::class,

            // 3. Users and Employees (needed for leave management)
            UserEmployeeSeeder::class,

            // 4. Leave Management (depends on users and organization structure)
            LeaveTypeSeeder::class,
            LeaveApproverSeeder::class, // Depends on department heads and users
            LeaveBalanceSeeder::class, // Depends on employees and leave types

            // Commented out seeders for reference
            // RolesAndPermissionsSeeder::class,
            // JobPostingSeeder::class,
            // UpdateHRPermissionsSeeder::class,
        ]);
    }
}

