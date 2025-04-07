<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Department;
use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserEmployeeSeeder extends Seeder
{
    /**
     * @throws \Exception
     */
    public function run()
    {
        $this->ensureRolesExist();

        // Get Departments and Units
        $hrDepartment = Department::where('name', 'Human Resources')->first();
        $hrUnit = OrganizationUnit::where('name', 'Human Resources')->first();
        $itDepartment = Department::where('name', 'SIMBA MONEY')->first();
        $itUnit = OrganizationUnit::where('name', 'SIMBA MONEY')->first();

        // Get Job Titles
        $groupCEOTitle = JobTitle::where('name', 'Group CEO')->first();
        $hrDirectorTitle = JobTitle::where('name', 'Group HR Director')->first();
        $ctoTitle = JobTitle::where('name', 'CTO')->first();
        $itOfficerTitle = JobTitle::where('name', 'IT Officer')->first();


        // Create Super Admin
        $admin = $this->createUserAndEmployee(
            'Thecla Denis Ntyangiri',
            'admin@simbagrp.com',
            'super_admin',
            $hrDepartment,
            $hrUnit,
            $hrDirectorTitle,
            null,
            'SGRP002',
            'female',
            '1982-11-07',
            1500000,
            '2022-11-14'
        );

        // Create CEO
        $ceo = $this->createUserAndEmployee(
            'David Lusan Ndelwa',
            'ceo@simbagrp.com',
            'chief_executive_officer',
            $hrDepartment,
            $hrUnit,
            $groupCEOTitle,
            null,
            'SLES28',
            'male',
            '1990-10-25',
            1500000,
            '2015-01-05'
        );

        // Create HR Director/Manager
        $hrManager = $this->createUserAndEmployee(
            'Harry Godfrey Mbise',
            'hr@simbagrp.com',
            'hr_manager',
            $hrDepartment,
            $hrUnit,
            $hrDirectorTitle,
            $ceo->employee->id,
            'SGRP005',
            'male',
            '1997-05-06',
            1400000,
            '2023-09-25'
        );

        // Create IT Manager (CTO)
        $itManager = $this->createUserAndEmployee(
            'Edgar Aidan Komba',
            'it.manager@simbagrp.com',
            'department_head',
            $itDepartment,
            $itUnit,
            $ctoTitle,
            $ceo->employee->id,
            '002/22/SML',
            'male',
            '1991-11-07',
            1500000,
            '2021-01-01'
        );

        // Create Regular IT Employee
        $developers = [
            [
                'name' => 'David John Haule',
                'email' => 'david.haule@simbagrp.com',
                'code' => '004/22/SML',
                'birthdate' => '1995-03-09',
                'salary' => 714000,
                'appointment_date' => '2021-10-21'
            ]
        ];

        foreach ($developers as $dev) {
            $this->createUserAndEmployee(
                $dev['name'],
                $dev['email'],
                'employee',
                $itDepartment,
                $itUnit,
                $itOfficerTitle,
                $itManager->employee->id,
                $dev['code'],
                'male',
                $dev['birthdate'],
                $dev['salary'],
                $dev['appointment_date']
            );
        }

        $this->updateHeadcounts([$hrDepartment, $itDepartment], [$hrUnit, $itUnit]);
    }

    private function createUserAndEmployee(
        $name,
        $email,
        $role,
        $department,
        $unit,
        $jobTitle,
        $reportingTo = null,
        $empCode = null,
        $gender = 'male',
        $birthdate = '1990-01-01',
        $salary = null,
        $appointmentDate = null
    )
    {
        // Create or update user
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password123'),
                'email_verified_at' => now()
            ]
        );

        // Sync role
        if (!$user->hasRole($role)) {
            $user->syncRoles([$role]);
        }

        $nameParts = explode(' ', $name);

        // Create or update employee
        $employee = Employee::updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_code' => $empCode ?? $this->generateEmployeeCode(),
                'first_name' => $nameParts[0],
                'last_name' => end($nameParts),
                'middle_name' => count($nameParts) > 2 ? $nameParts[1] : null,
                'gender' => $gender,
                'birthdate' => $birthdate,
                'marital_status' => 'single',
                'phone_number' => '+255742' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'email' => $email,
                'permanent_address' => 'Dar es Salaam',
                'city' => 'Dar es Salaam',
                'state' => 'Dar es Salaam',
                'postal_code' => '12345',
                'job_title_id' => $jobTitle->id,
                'department_id' => $department ? $department->id : null,
                'unit_id' => $unit ? $unit->id : null,
                'net_salary' => $salary ?? $jobTitle->net_salary_min,
                'employment_status' => 'active',
                'application_status' => 'active',
                'contract_type' => 'contract',
                'terms_of_employment' => 'full-time',
                'appointment_date' => $appointmentDate ?? now(),
                'reporting_to' => $reportingTo
            ]
        );

        $user->employee = $employee;
        return $user;
    }

    private function generateEmployeeCode()
    {
        $prefix = 'SLES';
        $count = Employee::count() + 1;
        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    private function updateHeadcounts($departments, $units): void
    {
        foreach ($departments as $department) {
            if ($department) {
                $department->update([
                    'current_headcount' => $department->employees()->count()
                ]);
            }
        }

        foreach ($units as $unit) {
            if ($unit) {
                $unit->update([
                    'current_headcount' => $unit->employees()->count()
                ]);
            }
        }
    }

    private function ensureRolesExist(): void
    {
        $roles = [
            'super_admin',
            'chief_executive_officer',
            'hr_manager',
            'department_head',
            'employee'
        ];

        foreach ($roles as $role) {
            if (!Role::where('name', $role)->exists()) {
                $this->command->warn("Role {$role} does not exist. Make sure to run ShieldSeeder first.");
                throw new \Exception("Required role {$role} does not exist. Please run 'php artisan db:seed --class=ShieldSeeder' first.");
            }
        }
    }
}
