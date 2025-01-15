<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeProfileController extends Controller
{
    public function download(Employee $employee)
    {
        $pdf = PDF::loadView('admin.employees.profile-pdf', [
            'filament' => $employee
        ]);

        return $pdf->download("filament-{$employee->employee_code}.pdf");
    }
}
