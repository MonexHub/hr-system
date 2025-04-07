<?php

namespace App\Http\Controllers\Api;

use App\Models\PerformanceAppraisal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceAppraisalController extends Controller
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
            $validator = Validator::make($request->all(), [
                'employee_id' => 'nullable|exists:employees,id',
                'immediate_supervisor_id' => 'nullable|exists:employees,id',
                'evaluation_date' => 'required|date',
                'evaluation_period_start' => 'required|date',
                'evaluation_period_end' => 'required|date|after_or_equal:evaluation_period_start',
                'objectives' => 'required|array|min:1',
                'objectives.*.objective' => 'required|string',
                'objectives.*.completion_date' => 'required|date',
                'objectives.*.rating' => 'nullable|numeric',
                'objectives.*.supervisor_feedback' => 'nullable|string',
            ]);

            if (!isset($request->employee_id)) {
                $request->merge([
                    'employee_id' => Auth::user()->employee->id,
                ]);
            }

            if (!isset($request->immediate_supervisor_id)) {
                $supervisorId = Auth::user()->employee->reporting_to;
                if ($supervisorId) {
                    $request->merge([
                        'immediate_supervisor_id' => $supervisorId,
                    ]);
                }
            }

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->except('objectives');
            $data['status'] = PerformanceAppraisal::STATUS_DRAFT;

            $appraisal = PerformanceAppraisal::create($data);

            if ($request->has('objectives')) {
                $appraisal->objectives()->createMany($request->objectives);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Appraisal created.',
                'data' => $appraisal->load('objectives'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create appraisal: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while creating the appraisal.',
                'data' => null
            ], 500);
        }
    }

    public function show(PerformanceAppraisal $appraisal)
    {
        $appraisal->load(['employee', 'supervisor', 'objectives']);
        return response()->json(['status' => 'success', 'data' => $appraisal]);
    }

    public function update(Request $request, PerformanceAppraisal $appraisal)
    {
        $appraisal->update($request->all());
        return response()->json(['status' => 'success', 'message' => 'Appraisal updated.', 'data' => $appraisal]);
    }

    public function destroy(PerformanceAppraisal $appraisal)
    {
        $appraisal->delete();
        return response()->json(['status' => 'success', 'message' => 'Appraisal deleted.']);
    }

    public function submit(PerformanceAppraisal $appraisal)
    {
        if ($appraisal->submit()) {
            return response()->json(['status' => 'success', 'message' => 'Appraisal submitted.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Appraisal cannot be submitted.'], 400);
    }

    public function supervisorApprove(PerformanceAppraisal $appraisal)
    {
        if ($appraisal->supervisorApprove()) {
            return response()->json(['status' => 'success', 'message' => 'Appraisal approved by supervisor.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Approval failed.'], 400);
    }

    public function hrApprove(PerformanceAppraisal $appraisal)
    {
        if ($appraisal->hrApprove()) {
            return response()->json(['status' => 'success', 'message' => 'Appraisal approved and completed.']);
        }

        return response()->json(['status' => 'error', 'message' => 'HR approval failed.'], 400);
    }
}
