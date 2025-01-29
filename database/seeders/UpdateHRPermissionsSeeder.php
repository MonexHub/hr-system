<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdateHRPermissionsSeeder extends Seeder
{
    public function run()
    {
        $hrRole = Role::findByName('hr_manager');

        $permissions = array(
            // Employee management
            'view_any_employee',
            'view_employee',
            'create_employee',
            'update_employee',
            'delete_employee',
            // Department management
            'view_any_department',
            'view_department',
            // Leave management
            'view_any_leave_request',
            'view_leave_request',
            'update_leave_request',
            'view_any_leave_type',
            'view_leave_type'
        );

        $hrRole->syncPermissions($permissions);
    }
}
