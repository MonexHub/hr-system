<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $roles = [
            'super_admin' => 'Super Administrator',
            'hr_manager' => 'HR Manager',
            'department_manager' => 'Department Manager',
            'employee' => 'Employee',
        ];

        foreach ($roles as $key => $name) {
            Role::create(['name' => $key]);
        }

        // Define Permissions for Admin Panel
        $adminPermissions = [
            // Employee Management
            'employee' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any',
                'export', 'import', 'manage_roles'
            ],

            // Department Management
            'department' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any'
            ],

            // Organization Management
            'organization_unit' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any',
                'manage_hierarchy'
            ],

            // Leave Management (Admin)
            'leave_type' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any'
            ],
            'leave_request' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any',
                'approve', 'reject'
            ],
            'leave_balance' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any'
            ],

            // Recruitment
            'job_posting' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any',
                'publish', 'unpublish'
            ],
            'candidate' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any'
            ],
            'job_application' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any'
            ],
            'interview_schedule' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any'
            ],
            'job_offer' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any'
            ],

            // Settings
            'job_title' => [
                'view', 'view_any', 'create', 'update', 'delete', 'delete_any'
            ],
        ];

        // Define Permissions for Employee Panel
        $employeePermissions = [
            // Profile Management
            'profile' => [
                'view', 'view_any', 'update'
            ],

            // Leave Management (Employee)
            'employee_leave_request' => [
                'view', 'view_any', 'create', 'cancel'
            ],

            // Job Applications (Employee)
            'employee_job_posting' => [
                'view', 'view_any', 'apply'
            ],

            // Employee Documents
            'document' => [
                'view', 'view_any', 'create', 'update', 'delete'
            ],

            // Education Records
            'education' => [
                'view', 'view_any', 'create', 'update', 'delete'
            ],

            // Dependents
            'dependent' => [
                'view', 'view_any', 'create', 'update', 'delete'
            ],

            // Skills
            'skill' => [
                'view', 'view_any', 'create', 'update', 'delete'
            ],

            // Emergency Contacts
            'emergency_contact' => [
                'view', 'view_any', 'create', 'update', 'delete'
            ],
        ];

        // Create all permissions
        foreach (array_merge($adminPermissions, $employeePermissions) as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::create(['name' => "${action}_${resource}"]);
            }
        }

        // Assign permissions to roles
        $rolePermissions = [
            // Super Admin gets everything
            'super_admin' => Permission::all()->pluck('name')->toArray(),

            // HR Manager Permissions
            'hr_manager' => [
                // Employee Management
                'view_employee', 'view_any_employee', 'create_employee', 'update_employee',
                'export_employee', 'import_employee',

                // Department Management
                'view_department', 'view_any_department', 'create_department', 'update_department',

                // Leave Management
                'view_leave_type', 'view_any_leave_type', 'create_leave_type', 'update_leave_type',
                'view_leave_request', 'view_any_leave_request', 'approve_leave_request', 'reject_leave_request',
                'view_leave_balance', 'view_any_leave_balance', 'update_leave_balance',

                // Recruitment Management
                'view_job_posting', 'view_any_job_posting', 'create_job_posting', 'update_job_posting',
                'view_candidate', 'view_any_candidate', 'create_candidate', 'update_candidate',
                'view_job_application', 'view_any_job_application', 'update_job_application',
                'view_interview_schedule', 'view_any_interview_schedule', 'create_interview_schedule',
                'view_job_offer', 'view_any_job_offer', 'create_job_offer', 'update_job_offer',
            ],

            // Department Manager Permissions
            'department_manager' => [
                // Employee viewing
                'view_employee', 'view_any_employee',

                // Leave Management
                'view_leave_request', 'view_any_leave_request', 'approve_leave_request', 'reject_leave_request',
                'view_leave_balance', 'view_any_leave_balance',

                // Limited recruitment viewing
                'view_job_posting', 'view_any_job_posting',
                'view_job_application', 'view_any_job_application',
                'view_interview_schedule', 'view_any_interview_schedule',
            ],

            // Employee Permissions (Employee Panel)
            'employee' => [
                // Profile Management
                'view_profile', 'view_any_profile', 'update_profile',

                // Leave Management
                'view_employee_leave_request', 'view_any_employee_leave_request',
                'create_employee_leave_request', 'cancel_employee_leave_request',

                // Job Applications
                'view_employee_job_posting', 'view_any_employee_job_posting', 'apply_employee_job_posting',

                // Documents Management
                'view_document', 'view_any_document', 'create_document', 'update_document', 'delete_document',

                // Education Records
                'view_education', 'view_any_education', 'create_education', 'update_education', 'delete_education',

                // Dependents
                'view_dependent', 'view_any_dependent', 'create_dependent', 'update_dependent', 'delete_dependent',

                // Skills
                'view_skill', 'view_any_skill', 'create_skill', 'update_skill', 'delete_skill',

                // Emergency Contacts
                'view_emergency_contact', 'view_any_emergency_contact', 'create_emergency_contact',
                'update_emergency_contact', 'delete_emergency_contact',
            ],
        ];

        // Assign permissions to roles
        foreach ($rolePermissions as $role => $permissions) {
            Role::findByName($role)->givePermissionTo($permissions);
        }

        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $superAdmin->assignRole('super_admin');
    }
}
