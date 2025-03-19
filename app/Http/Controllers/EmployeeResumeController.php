<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeResumeController extends Controller
{
    public function show(Employee $employee)
    {
        // Authorization check - only allow if user is authorized to view this employee
        if (Auth::user()->cannot('view', $employee)) {
            abort(403);
        }

        // Load all the employee relationships
        $employee->load([
            'department',
            'jobTitle',
            'reportingTo',
            'dependents',
            'emergencyContacts',
            'skills',
            'documents',
            'education',
            'financials',
            'user'
        ]);

        return view('employee.resume', compact('employee'));
    }
}
