<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = json_encode([
            [
                'name' => 'super_admin',
                'guard_name' => 'web',
                'permissions' => array_merge(
                    $this->getAdminPanelPermissions(),
                    $this->getEmployeePanelPermissions(),
                    $this->getPagePermissions()
                )
            ],
            [
                'name' => 'chief_executive_officer',
                'guard_name' => 'web',
                'permissions' => $this->getCeoPermissions()
            ],
            [
                'name' => 'hr_manager',
                'guard_name' => 'web',
                'permissions' => $this->getHrManagerPermissions()
            ],
            [
                'name' => 'department_head',
                'guard_name' => 'web',
                'permissions' => $this->getDepartmentHeadPermissions()
            ],
            [
                'name' => 'employee',
                'guard_name' => 'web',
                'permissions' => $this->getEmployeePermissions()
            ]
        ]);

        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);
    }

    protected function getAdminPanelPermissions(): array
    {
        return [
            // User Management
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',

            // Employee Management
            'view_employee',
            'view_any_employee',
            'create_employee',
            'update_employee',
            'delete_employee',
            'delete_any_employee',
            'export_employee',
            'import_employee',

            // Organization Management
            'view_organization_unit',
            'view_any_organization_unit',
            'create_organization_unit',
            'update_organization_unit',
            'delete_organization_unit',

            'view_department',
            'view_any_department',
            'create_department',
            'update_department',
            'delete_department',

            // Leave Management
            'view_leave_type',
            'view_any_leave_type',
            'create_leave_type',
            'update_leave_type',
            'delete_leave_type',

            'view_leave_request',
            'view_any_leave_request',
            'create_leave_request',
            'update_leave_request',
            'delete_leave_request',
            'manage_all_leave_requests',

            // AppSettings
            'manage_system_settings',
            'manage_roles',
            'manage_permissions',

            //Performance Management
            'submit_performance::appraisal',
            'supervisor_approve_performance::appraisal',
            'hr_approve_performance::appraisal'
        ];
    }

    protected function getEmployeePanelPermissions(): array
    {
        return [
            // Profile Management
            'view_profile',
            'update_profile',
            'view_documents',
            'upload_documents',

            // Leave Management
            'view_own_leave_requests',
            'create_leave_request',
            'cancel_own_leave_request',
            'view_leave_balance',

            // Time Management
            'view_attendance',
            'view_timesheet',
            'create_timesheet',
            'update_timesheet',

            // Performance
            'view_own_performance',
            'view_own_goals',
            'create_goal',
            'update_goal'
        ];
    }

    protected function getCeoPermissions(): array
    {
        return [
            // Organization Overview
            'view_organization_dashboard',
            'view_department_reports',
            'view_employee_reports',

            // Leave Management
            'view_leave_request',
            'view_any_leave_request',
            'approve_ceo_leave_request',
            'reject_leave_request',
            'view_leave_reports',

            // Performance Management
            'view_performance_dashboard',
            'view_any_performance_review',
            'approve_performance_review',

            // Employee Overview
            'view_employee',
            'view_any_employee',
            'view_employee_statistics'
        ];
    }

    protected function getHrManagerPermissions(): array
    {
        return [
            // Employee Management
            'view_employee',
            'view_any_employee',
            'create_employee',
            'update_employee',
            'manage_employee_documents',

            // Leave Management
            'view_leave_request',
            'view_any_leave_request',
            'approve_hr_leave_request',
            'reject_leave_request',
            'manage_leave_balance',

            // Organization Management
            'view_department',
            'view_any_department',
            'manage_job_titles',
            'manage_positions',

            // Reporting
            'view_hr_dashboard',
            'generate_hr_reports',
            'export_employee_data',

            // Performance Management
            'supervisor_approve_performance::appraisal',
            'hr_approve_performance::appraisal'
        ];
    }

    protected function getDepartmentHeadPermissions(): array
    {
        return [
            // Department Management
            'view_department_dashboard',
            'view_department_employees',
            'manage_department_schedule',


            // Leave Management
            'view_department_leave_requests',
            'approve_department_leave_request',
            'reject_department_leave_request',
            'view_leave_request',
            'view_any_leave_request',
            'approve_leave_request',
            'reject_leave_request',
            'view_department_leave_calendar',

            // Team Management
            'view_team_attendance',
            'view_team_performance',
            'manage_team_goals',

            // Performance Management
            'supervisor_approve_performance::appraisal'
        ];
    }

    protected function getEmployeePermissions(): array
    {
        return [
            // Profile
            'view_profile',
            'update_profile',
            'view_documents',

            //Perfomance Management
            'submit_performance::appraisal',

            // Leave
            'view_own_leave_requests',
            'create_leave_request',
            'cancel_own_leave_request',
            'view_leave_balance',

            // General Access
            'view_company_directory',
            'view_announcements',
            'view_employee_handbook'
        ];
    }

    protected function getPagePermissions(): array
    {
        return [
            'page_Dashboard',
            'page_Profile',
            'page_LeaveManagement',
            'page_Documents',
            'page_Directory',
            'page_Settings'
        ];
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            $roleModel = Utils::getRoleModel();
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    protected static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                $permissionModel::firstOrCreate([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
