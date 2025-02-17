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
        $itDepartment = Department::where('name', 'Software Development')->first();
        $itUnit = OrganizationUnit::where('name', 'Software Development')->first();

        // Get Job Titles
        $ceoTitle = JobTitle::where('name', 'Chief Executive Officer')->first();
        $hrManagerTitle = JobTitle::where('name', 'Human Resources Manager')->first();
        $devManagerTitle = JobTitle::where('name', 'Project Manager')->first();
        $devTitle = JobTitle::where('name', 'Software Engineer')->first();

        // Create Super Admin
        $admin = $this->createUserAndEmployee(
            'John Doe',
            'admin@monex.co.tz',
            'super_admin',
            $hrDepartment,
            $hrUnit,
            $hrManagerTitle
        );

        // Create CEO (separate user)
        $ceo = $this->createUserAndEmployee(
            'James Wilson',
            'ceo@monex.co.tz',
            'chief_executive_officer',
            $hrDepartment,
            $hrUnit,
            $ceoTitle
        );

        // Create HR Manager
        $hrManager = $this->createUserAndEmployee(
            'Jane Smith',
            'hr@monex.co.tz',
            'hr_manager',
            $hrDepartment,
            $hrUnit,
            $hrManagerTitle,
            $ceo->employee->id // Reports to CEO
        );

        // Create IT Manager (Department Head)
        $itManager = $this->createUserAndEmployee(
            'Mike Johnson',
            'it.manager@monex.co.tz',
            'department_head',
            $itDepartment,
            $itUnit,
            $devManagerTitle,
            $ceo->employee->id // Reports to CEO
        );

        // Create Regular Employees
        $developers = [
            [
                'name' => 'Alice Brown',
                'email' => 'alice@monex.co.tz',
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@monex.co.tz',
            ]
        ];

        foreach ($developers as $dev) {
            $this->createUserAndEmployee(
                $dev['name'],
                $dev['email'],
                'employee',
                $itDepartment,
                $itUnit,
                $devTitle,
                $itManager->employee->id // Reports to IT Manager
            );
        }

        $this->updateHeadcounts([$hrDepartment, $itDepartment], [$hrUnit, $itUnit]);
    }

    private function createUserAndEmployee($name, $email, $role, $department, $unit, $jobTitle, $reportingTo = null)
    {
        // Create or update user
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password123'),
                'email_verified_at' => now() // Add email verification
            ]
        );

        // Sync role instead of just assigning
        if (!$user->hasRole($role)) {
            $user->syncRoles([$role]);
        }

        $nameParts = explode(' ', $name);

        // Create or update employee with more fields
        $employee = Employee::updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_code' => $this->generateEmployeeCode(),
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'gender' => 'male',
                'birthdate' => '1990-01-01',
                'marital_status' => 'single',
                'phone_number' => '+255742' . str_pad(Employee::count() + 1, 6, '0', STR_PAD_LEFT),
                'email' => $email,
                'permanent_address' => 'Dar es Salaam',
                'city' => 'Dar es Salaam',
                'state' => 'Dar es Salaam',
                'postal_code' => '12345',
                'job_title_id' => $jobTitle->id,
                'department_id' => $department ? $department->id : null,
                'unit_id' => $unit ? $unit->id : null,
                'net_salary' => $jobTitle->net_salary_min,
                'employment_status' => 'active',
                'application_status' => 'active',
                'contract_type' => 'permanent',
                'terms_of_employment' => 'full-time',
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
