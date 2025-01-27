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

        // Create roles
        $roles = array(
            'super_admin',
            'hr_manager',
            'department_manager',
            'employee'
        );

        foreach ($roles as $role) {
            Role::create(array('name' => $role));
        }

        // Create permissions for each model
        $models = array(
            'employee',
            'department',
            'leave_request',
            'leave_type',
            'organization_unit'
        );

        $actions = array(
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
            'import',
            'export'
        );

        foreach ($models as $model) {
            foreach ($actions as $action) {
                Permission::create(array('name' => $action . '_' . $model));
            }
        }

        // Assign permissions to roles
        $superAdmin = Role::findByName('super_admin');
        $superAdmin->givePermissionTo(Permission::all());

        $hrManager = Role::findByName('hr_manager');
        $hrManager->givePermissionTo(array(
            'view_any_employee',
            'view_employee',
            'create_employee',
            'update_employee',
            'delete_employee',
            'import_employee',
            'export_employee',
            'view_any_department',
            'view_department',
            'view_any_leave_request',
            'view_leave_request',
            'update_leave_request'
        ));

        $deptManager = Role::findByName('department_manager');
        $deptManager->givePermissionTo(array(
            'view_any_employee',
            'view_employee',
            'view_any_leave_request',
            'view_leave_request',
            'update_leave_request'
        ));

        $employee = Role::findByName('employee');
        $employee->givePermissionTo(array(
            'view_employee',
            'view_leave_request',
            'create_leave_request'
        ));
    }
}
