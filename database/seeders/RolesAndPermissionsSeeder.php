<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // HR System Permissions
        $permissions = [
            'view_employees',
            'create_employees',
            'edit_employees',
            'delete_employees',
            'view_leave_requests',
            'approve_leave_requests',
            'reject_leave_requests',
            'manage_departments',
            'view_reports',
            'manage_roles',
            'view_payroll',
            'manage_payroll'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles with permissions
        $roles = [
            'super_admin' => $permissions,
            'hr_manager' => [
                'view_employees', 'create_employees', 'edit_employees',
                'view_leave_requests', 'approve_leave_requests', 'reject_leave_requests',
                'manage_departments', 'view_reports', 'view_payroll', 'manage_payroll'
            ],
            'department_manager' => [
                'view_employees',
                'view_leave_requests', 'approve_leave_requests', 'reject_leave_requests',
                'view_reports'
            ],
            'employee' => [
                'view_leave_requests'
            ]
        ];

        foreach ($roles as $role => $rolePermissions) {
            $roleInstance = Role::create(['name' => $role]);
            $roleInstance->givePermissionTo($rolePermissions);
        }
    }
}
