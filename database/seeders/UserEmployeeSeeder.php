<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $hrDepartment = Department::where('name', 'Human Resources')->first();
        $hrUnit = OrganizationUnit::where('name', 'Human Resources')->first();
        $itDepartment = Department::where('name', 'Software Development')->first();
        $itUnit = OrganizationUnit::where('name', 'Software Development')->first();

        // Create Super Admin
        $admin = $this->createUserAndEmployee(
            'Admin User',
            'admin@monex.co.tz',
            'super_admin',
            $hrDepartment,
            $hrUnit,
            'Administrator'
        );

        // Create HR Manager
        $hrManager = $this->createUserAndEmployee(
            'Edgar Aidan',
            'edgar@monex.co.tz',
            'hr_manager',
            $hrDepartment,
            $hrUnit,
            'HR Manager',
            $admin->employee->id
        );

        // Create IT Manager
        $itManager = $this->createUserAndEmployee(
            'James Kanga',
            'james@monex.co.tz',
            'department_manager',
            $itDepartment,
            $itUnit,
            'IT Manager',
            $hrManager->employee->id
        );

        // Create Developer
        $this->createUserAndEmployee(
            'David John',
            'david@monex.co.tz',
            'employee',
            $itDepartment,
            $itUnit,
            'Software Developer',
            $itManager->employee->id
        );

        $this->updateHeadcounts([$hrDepartment, $itDepartment], [$hrUnit, $itUnit]);
    }

    private function createUserAndEmployee($name, $email, $role, $department, $unit, $jobTitle, $reportingTo = null)
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123')
        ]);

        $user->assignRole($role);

        $nameParts = explode(' ', $name);
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP' . str_pad(Employee::count() + 1, 3, '0', STR_PAD_LEFT),
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? '',
            'gender' => 'male',
            'birthdate' => '1990-01-01',
            'phone_number' => '+255742' . str_pad(Employee::count() + 1, 6, '0', STR_PAD_LEFT),
            'email' => $email,
            'permanent_address' => 'Dar es Salaam',
            'city' => 'Dar es Salaam',
            'state' => 'Dar es Salaam',
            'postal_code' => '12345',
            'job_title' => $jobTitle,
            'department_id' => $department?->id,
            'unit_id' => $unit?->id,
            'salary' => match($role) {
                'super_admin' => 3500000,
                'hr_manager' => 3000000,
                'department_manager' => 2500000,
                default => 1800000,
            },
            'employment_status' => 'active',
            'application_status' => 'active',
            'contract_type' => 'permanent',
            'appointment_date' => now(),
            'reporting_to' => $reportingTo
        ]);

        $user->employee = $employee;
        return $user;
    }

    private function updateHeadcounts(?array $departments, ?array $units): void
    {
        foreach ($departments as $department) {
            if ($department) {
                $department->update(['current_headcount' => $department->employees()->count()]);
            }
        }

        foreach ($units as $unit) {
            if ($unit) {
                $unit->update(['current_headcount' => $unit->employees()->count()]);
            }
        }
    }
}
