<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserEmployeeSeeder extends Seeder
{
    public function run()
    {
        $this->ensureRolesExist();

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

        $this->updateHeadcounts(array($hrDepartment, $itDepartment), array($hrUnit, $itUnit));
    }

    private function createUserAndEmployee($name, $email, $role, $department, $unit, $jobTitle, $reportingTo = null)
    {
        // Check if user exists
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password123')
            ]
        );

        // Ensure role exists and assign
        if (!$user->hasRole($role)) {
            $user->assignRole($role);
        }

        $nameParts = explode(' ', $name);

        // Create or update employee
        $employee = Employee::updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_code' => $this->generateEmployeeCode(),
                'first_name' => $nameParts[0],
                'last_name' => isset($nameParts[1]) ? $nameParts[1] : '',
                'gender' => 'male',
                'birthdate' => '1990-01-01',
                'phone_number' => '+255742' . str_pad(Employee::count() + 1, 6, '0', STR_PAD_LEFT),
                'email' => $email,
                'permanent_address' => 'Dar es Salaam',
                'city' => 'Dar es Salaam',
                'state' => 'Dar es Salaam',
                'postal_code' => '12345',
                'job_title' => $jobTitle,
                'department_id' => $department ? $department->id : null,
                'unit_id' => $unit ? $unit->id : null, // Changed from organization_unit_id to unit_id
                'net_salary' => $this->getSalaryByRole($role),
                'employment_status' => 'active',
                'contract_type' => 'permanent',
                'appointment_date' => now(),
                'reporting_to' => $reportingTo
            ]
        );

        $user->employee = $employee;
        return $user;
    }

    private function generateEmployeeCode()
    {
        $prefix = 'EMP';
        $count = Employee::count() + 1;
        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    private function getSalaryByRole($role)
    {
        $salaries = array(
            'super_admin' => 3500000,
            'hr_manager' => 3000000,
            'department_manager' => 2500000
        );
        return isset($salaries[$role]) ? $salaries[$role] : 1800000;
    }

    private function updateHeadcounts($departments, $units)
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

    private function ensureRolesExist()
    {
        $roles = ['super_admin', 'hr_manager', 'department_manager', 'employee'];

        foreach ($roles as $role) {
            if (!Role::where('name', $role)->exists()) {
                $this->command->warn("Role {$role} does not exist. Make sure to run ShieldSeeder first.");
                throw new \Exception("Required role {$role} does not exist. Please run 'php artisan db:seed --class=ShieldSeeder' first.");
            }
        }
    }
}
