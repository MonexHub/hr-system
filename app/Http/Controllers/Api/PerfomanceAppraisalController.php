<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PerformanceAppraisal;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

class PerfomanceAppraisalController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $query = PerformanceAppraisal::query()->with(['employee', 'supervisor', 'objectives']);

            if ($user->hasRole('employee')) {
                $query->where('employee_id', $user->employee->id);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Performance appraisals retrieved successfully',
                'data' => $query->latest()->get()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch appraisals: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not fetch appraisals',
                'data' => null
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
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

            return response()->json([
                'status' => 'success',
                'message' => 'Appraisal created',
                'data' => $appraisal->load('employee', 'supervisor')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create appraisal: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not create appraisal',
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $appraisal = PerformanceAppraisal::with(['employee', 'supervisor', 'objectives'])->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Appraisal retrieved',
                'data' => $appraisal
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to fetch appraisal with ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Appraisal not found',
                'data' => null
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $appraisal = PerformanceAppraisal::findOrFail($id);
            $appraisal->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Appraisal updated',
                'data' => $appraisal->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update appraisal with ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Update failed',
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $appraisal = PerformanceAppraisal::findOrFail($id);
            $appraisal->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Appraisal soft deleted',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to delete appraisal with ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'data' => null
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $appraisal = PerformanceAppraisal::withTrashed()->findOrFail($id);
            $appraisal->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Appraisal restored',
                'data' => $appraisal
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to restore appraisal with ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Restore failed',
                'data' => null
            ], 500);
        }
    }

    public function submit($id)
    {
        try {
            $appraisal = PerformanceAppraisal::findOrFail($id);
            $appraisal->status = 'submitted';
            $appraisal->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Appraisal submitted',
                'data' => $appraisal
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to submit appraisal with ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Submit failed',
                'data' => null
            ], 500);
        }
    }

    public function supervisorApprove($id)
    {
        try {
            $appraisal = PerformanceAppraisal::findOrFail($id);
            $appraisal->status = 'supervisor_approved';
            $appraisal->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Appraisal approved by supervisor',
                'data' => $appraisal
            ]);
        } catch (\Exception $e) {
            Log::error("Supervisor approval failed for appraisal ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Supervisor approval failed',
                'data' => null
            ], 500);
        }
    }

    public function hrApprove($id)
    {
        try {
            $appraisal = PerformanceAppraisal::findOrFail($id);
            $appraisal->status = 'hr_approved';
            $appraisal->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Appraisal approved by HR',
                'data' => $appraisal
            ]);
        } catch (\Exception $e) {
            Log::error("HR approval failed for appraisal ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'HR approval failed',
                'data' => null
            ], 500);
        }
    }
}
