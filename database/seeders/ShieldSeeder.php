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
                'name' => 'hr_manager',
                'guard_name' => 'web',
                'permissions' => $this->getHrManagerPermissions()
            ],
            [
                'name' => 'department_manager',
                'guard_name' => 'web',
                'permissions' => $this->getDepartmentManagerPermissions()
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

        $this->command->info('Shield Seeding Completed.');
    }

    protected function getAdminPanelPermissions(): array
    {
        $resources = [
            'role' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'],
            'employee' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any',
                'export', 'import', 'manage_roles'],
            'department' => ['view', 'view_any', 'create', 'update', 'restore', 'restore_any',
                'replicate', 'reorder', 'delete', 'delete_any', 'force_delete',
                'force_delete_any'],
            'organization::unit' => ['view', 'view_any', 'create', 'update', 'restore',
                'restore_any', 'replicate', 'reorder', 'delete', 'delete_any',
                'force_delete', 'force_delete_any'],
            'leave::type' => ['view', 'view_any', 'create', 'update', 'restore', 'restore_any',
                'replicate', 'reorder', 'delete', 'delete_any', 'force_delete',
                'force_delete_any'],
            'leave::request' => ['view', 'view_any', 'create', 'update', 'restore', 'restore_any',
                'replicate', 'reorder', 'delete', 'delete_any', 'force_delete',
                'force_delete_any',   'approve_manager', 'reject', 'create_for_others',
                        ],
            'leave::balance' => ['view', 'view_any', 'create', 'update', 'restore', 'restore_any',
                'replicate', 'reorder', 'delete', 'delete_any', 'force_delete',
                'force_delete_any'],
            'job::posting' => ['view', 'view_any', 'create', 'update', 'restore', 'restore_any',
                'replicate', 'reorder', 'delete', 'delete_any', 'force_delete',
                'force_delete_any'],
            'job::application' => ['view', 'view_any', 'create', 'update', 'restore', 'restore_any',
                'replicate', 'reorder', 'delete', 'delete_any', 'force_delete',
                'force_delete_any'],
            'job::offer' => ['view', 'view_any', 'create', 'update', 'restore', 'restore_any',
                'replicate', 'reorder', 'delete', 'delete_any', 'force_delete',
                'force_delete_any'],
            'job::title' => ['view', 'view_any', 'create', 'update', 'restore', 'restore_any',
                'replicate', 'reorder', 'delete', 'delete_any', 'force_delete',
                'force_delete_any'],
            'interview::schedule' => ['view', 'view_any', 'create', 'update', 'restore',
                'restore_any', 'replicate', 'reorder', 'delete', 'delete_any',
                'force_delete', 'force_delete_any'],
            'candidate' => ['view', 'view_any', 'create', 'update', 'restore', 'restore_any',
                'replicate', 'reorder', 'delete', 'delete_any', 'force_delete',
                'force_delete_any']
        ];

        $permissions = [];
        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "${action}_${resource}";
            }
        }

        return $permissions;
    }

    protected function getEmployeePanelPermissions(): array
    {
        return [
            'view_profile', 'view_any_profile', 'create_profile', 'update_profile',
            'restore_profile', 'restore_any_profile', 'replicate_profile',
            'reorder_profile', 'delete_profile', 'delete_any_profile',
            'force_delete_profile', 'force_delete_any_profile',

            'view_employee::leave::request', 'view_any_employee::leave::request',
            'create_employee::leave::request', 'update_employee::leave::request',
            'restore_employee::leave::request', 'restore_any_employee::leave::request',
            'replicate_employee::leave::request', 'reorder_employee::leave::request',
            'delete_employee::leave::request', 'delete_any_employee::leave::request',
            'force_delete_employee::leave::request', 'force_delete_any_employee::leave::request'
        ];
    }

    protected function getPagePermissions(): array
    {
        return [
            'page_OrganizationStructure',
            'page_CompleteProfile'
        ];
    }

    protected function getHrManagerPermissions(): array
    {
        return [
            // Employee Management
            'view_employee', 'view_any_employee', 'create_employee', 'update_employee',
            'export_employee', 'import_employee',

            // Department Management
            'view_department', 'view_any_department', 'create_department', 'update_department',

            // Leave Management
            'view_leave::type', 'view_any_leave::type', 'create_leave::type', 'update_leave::type',
            'view_leave::request', 'view_any_leave::request', 'create_leave::request',
            'view_leave::balance', 'view_any_leave::balance', 'update_leave::balance',
            'approve_leave_request',
            'reject_leave::request',
            'create_for_others_leave::request',


            // Recruitment
            'view_job::posting', 'view_any_job::posting', 'create_job::posting',
            'view_candidate', 'view_any_candidate', 'create_candidate',
            'view_job::application', 'view_any_job::application',
            'view_interview::schedule', 'view_any_interview::schedule',
            'view_job::offer', 'view_any_job::offer', 'create_job::offer',

            'page_OrganizationStructure'
        ];
    }

    protected function getDepartmentManagerPermissions(): array
    {
        return [
            // Employee Access
            'view_employee', 'view_any_employee',

            // Leave Management
            'view_leave::request', 'view_any_leave::request',
            'view_leave::balance', 'view_any_leave::balance',

            'approve_manager_leave::request',  //
            'reject_leave::request',          //

            // Limited Recruitment Access
            'view_job::posting', 'view_any_job::posting',
            'view_job::application', 'view_any_job::application',
            'view_interview::schedule', 'view_any_interview::schedule',

            'page_OrganizationStructure'
        ];
    }

    protected function getEmployeePermissions(): array
    {
        return [
            // Profile Management
            'view_profile', 'view_any_profile', 'update_profile',
            'page_CompleteProfile',

            // Leave Management
            'view_employee::leave::request', 'view_any_employee::leave::request',
            'create_employee::leave::request',
            'update_employee::leave::request',

            // Job Portal Access
            'view_job::posting', 'view_any_job::posting'

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

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
