<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\EmployeeEducation;
use App\Models\EmployeeSkill;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeTraining;
use App\Models\EmployeeWorkExperience;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create HR Manager
        $hrManager = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@example.com',
            'password' => Hash::make('@Dmin2021!'),
        ]);
        $hrManager->assignRole('hr');

        $hrEmployee = Employee::create([
            'user_id' => $hrManager->id,
            'employee_code' => 'HR001',
            'first_name' => 'Juma',
            'last_name' => 'Hamisi',
            'middle_name' => 'Said',
            'gender' => 'male',
            'birthdate' => '1990-01-01',
            'phone_number' => '+255742000001',
            'permanent_address' => 'Plot 123, Masaki',
            'city' => 'Dar es Salaam',
            'state' => 'Dar es Salaam',
            'postal_code' => '12345',
            'contract_type' => 'permanent',
            'terms_of_employment' => 'full-time',
            'appointment_date' => '2023-01-01',
            'job_title' => 'HR Manager',
            'branch' => 'Head Office',
            'department_id' => 1,
            'salary' => 2500000,
            'employment_status' => 'active',
            'application_status' => 'active',
        ]);

        // HR Manager's Emergency Contact
        EmployeeEmergencyContact::create([
            'employee_id' => $hrEmployee->id,
            'name' => 'Fatuma Ali',
            'relationship' => 'sister',
            'phone' => '+255742000006',
            'alternative_phone' => '+255742000007',
            'email' => 'fatuma@example.com',
            'address' => 'P.O. Box 12345, Dar es Salaam',
        ]);

        // HR Manager's Education
        EmployeeEducation::create([
            'employee_id' => $hrEmployee->id,
            'institution' => 'University of Dar es Salaam',
            'degree' => 'Bachelor Degree',
            'field_of_study' => 'Human Resource Management',
            'start_date' => '2008-01-01',
            'end_date' => '2012-12-31',
            'grade' => 3.8,
        ]);

        // HR Manager's Skills
        EmployeeSkill::create([
            'employee_id' => $hrEmployee->id,
            'skill_name' => 'Human Resource Management',
            'proficiency_level' => 'expert',
            'category' => 'technical',
            'years_of_experience' => 10,
        ]);

        // Create IT Manager
        $itManager = User::create([
            'name' => 'IT Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
        ]);
        $itManager->assignRole('manager');

        $itEmployee = Employee::create([
            'user_id' => $itManager->id,
            'employee_code' => 'IT001',
            'first_name' => 'Abdul',
            'last_name' => 'Rahman',
            'middle_name' => 'Kassim',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'phone_number' => '+255742000008',
            'permanent_address' => 'Plot 456, Mikocheni',
            'city' => 'Dar es Salaam',
            'state' => 'Dar es Salaam',
            'postal_code' => '12345',
            'contract_type' => 'permanent',
            'terms_of_employment' => 'full-time',
            'appointment_date' => '2023-01-01',
            'job_title' => 'IT Manager',
            'branch' => 'Head Office',
            'department_id' => 2,
            'salary' => 3000000,
            'employment_status' => 'active',
            'application_status' => 'active',
            'reporting_to' => $hrEmployee->id,
        ]);

        // Create Regular Employee
        $employee = User::create([
            'name' => 'Software Developer',
            'email' => 'filament@example.com',
            'password' => Hash::make('password'),
        ]);
        $employee->assignRole('filament');

        $devEmployee = Employee::create([
            'user_id' => $employee->id,
            'employee_code' => 'IT002',
            'first_name' => 'Baraka',
            'last_name' => 'John',
            'middle_name' => 'Peter',
            'gender' => 'male',
            'birthdate' => '1995-01-01',
            'phone_number' => '+255742000011',
            'permanent_address' => 'Plot 789, Kinondoni',
            'city' => 'Dar es Salaam',
            'state' => 'Dar es Salaam',
            'postal_code' => '12345',
            'contract_type' => 'permanent',
            'terms_of_employment' => 'full-time',
            'appointment_date' => '2023-01-01',
            'job_title' => 'Software Developer',
            'branch' => 'Head Office',
            'department_id' => 2,
            'salary' => 1800000,
            'employment_status' => 'active',
            'application_status' => 'active',
            'reporting_to' => $itEmployee->id,
        ]);

        // Developer's Education
        EmployeeEducation::create([
            'employee_id' => $devEmployee->id,
            'institution' => 'University of Dodoma',
            'degree' => 'Bachelor Degree',
            'field_of_study' => 'Computer Science',
            'start_date' => '2014-01-01',
            'end_date' => '2018-12-31',
            'grade' => 3.5,
        ]);

        // Developer's Skills
        EmployeeSkill::create([
            'employee_id' => $devEmployee->id,
            'skill_name' => 'Software Development',
            'proficiency_level' => 'expert',
            'category' => 'technical',
            'years_of_experience' => 5,
        ]);
    }
}
