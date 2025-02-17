<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveApprover;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveApproverSeeder extends Seeder
{
    public function run()
    {
        // Get the first admin user for created_by
        $admin = User::role('super_admin')->first();
        if (!$admin) {
            throw new \Exception('Super admin user not found. Please run UserEmployeeSeeder first.');
        }

        // Get necessary roles and departments
        $ceo = User::role('chief_executive_officer')->first();
        $hrManager = User::role('hr_manager')->first();
        $departmentHeads = User::role('department_head')->get();

        // Get HR department for HR Manager
        $hrDepartment = Department::where('name', 'Human Resources')->first();
        if (!$hrDepartment) {
            throw new \Exception('HR Department not found. Please run DepartmentSeeder first.');
        }

        // Set up department heads as approvers
        foreach ($departmentHeads as $head) {
            $employee = Employee::where('user_id', $head->id)->first();
            if ($employee && $employee->department_id) {
                LeaveApprover::updateOrCreate(
                    [
                        'department_id' => $employee->department_id,
                        'approver_id' => $head->id,
                        'level' => 'department_head'
                    ],
                    [
                        'is_active' => true,
                        'can_approve_all_departments' => false,
                        'created_by' => $admin->id
                    ]
                );
            }
        }

        // Set up HR Manager as approver
        if ($hrManager) {
            $hrEmployee = Employee::where('user_id', $hrManager->id)->first();
            if ($hrEmployee && $hrDepartment) {
                LeaveApprover::updateOrCreate(
                    [
                        'department_id' => $hrDepartment->id,
                        'approver_id' => $hrManager->id,
                        'level' => 'hr'
                    ],
                    [
                        'is_active' => true,
                        'can_approve_all_departments' => true,
                        'created_by' => $admin->id
                    ]
                );
            }
        }

        // Set up CEO as top-level approver
        if ($ceo) {
            $ceoEmployee = Employee::where('user_id', $ceo->id)->first();
            if ($ceoEmployee && $hrDepartment) {
                LeaveApprover::updateOrCreate(
                    [
                        'department_id' => $hrDepartment->id, // Assign to HR department by default
                        'approver_id' => $ceo->id,
                        'level' => 'ceo'
                    ],
                    [
                        'is_active' => true,
                        'can_approve_all_departments' => true,
                        'created_by' => $admin->id
                    ]
                );
            }
        }
    }
}
