<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PerformanceAppraisal;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;



class PerfomanceAppraisalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = PerformanceAppraisal::query()->with(['employee', 'supervisor', 'objectives']);

        // Filter for employees
        if ($user->hasRole('employee')) {
            $query->where('employee_id', $user->employee->id);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $employeeId = $request->employee_id ?? $user->employee->id;

        $employee = Employee::with('department.manager')->findOrFail($employeeId);
        $supervisorId = $employee->reporting_to ?? optional($employee->department)->manager_id;

        $appraisal = PerformanceAppraisal::create([
            'employee_id' => $employeeId,
            'immediate_supervisor_id' => $supervisorId,
            'evaluation_date' => now(),
            'evaluation_period_start' => now()->startOfMonth(),
            'evaluation_period_end' => now()->endOfMonth(),
            'status' => 'draft',
        ]);

        return response()->json($appraisal->load('employee', 'supervisor'));
    }

    public function show($id)
    {
        $appraisal = PerformanceAppraisal::with(['employee', 'supervisor', 'objectives'])->findOrFail($id);
        return response()->json($appraisal);
    }

    public function update(Request $request, $id)
    {
        $appraisal = PerformanceAppraisal::findOrFail($id);
        $appraisal->update($request->all());

        return response()->json($appraisal->fresh());
    }

    public function destroy($id)
    {
        $appraisal = PerformanceAppraisal::findOrFail($id);
        $appraisal->delete();

        return response()->json(['message' => 'Soft deleted']);
    }

    public function restore($id)
    {
        $appraisal = PerformanceAppraisal::withTrashed()->findOrFail($id);
        $appraisal->restore();

        return response()->json(['message' => 'Restored']);
    }

    public function submit($id)
    {
        $appraisal = PerformanceAppraisal::findOrFail($id);
        $appraisal->status = 'submitted';
        $appraisal->save();

        return response()->json(['message' => 'Submitted']);
    }

    public function supervisorApprove($id)
    {
        $appraisal = PerformanceAppraisal::findOrFail($id);
        $appraisal->status = 'supervisor_approved';
        $appraisal->save();

        return response()->json(['message' => 'Supervisor approved']);
    }

    public function hrApprove($id)
    {
        $appraisal = PerformanceAppraisal::findOrFail($id);
        $appraisal->status = 'hr_approved';
        $appraisal->save();

        return response()->json(['message' => 'HR approved']);
    }
}
