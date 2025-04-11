<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ZKBiotimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZkbiotimeController extends Controller
{
    protected ZKBiotimeService $biotime;

    public function __construct(ZKBiotimeService $biotime)
    {
        $this->biotime = $biotime;
    }

    public function index(Request $request)
    {
        try {
            $employees = $this->biotime->getEmployees($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Employees retrieved',
                'data' => $employees,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch employees', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not fetch employees',
                'data' => null,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $employee = $this->biotime->getEmployee($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Employee retrieved',
                'data' => $employee,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to fetch employee #$id", ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not fetch employee',
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $employee = $this->biotime->createEmployee($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Employee created',
                'data' => $employee,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create employee', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not create employee',
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $employee = $this->biotime->updateEmployee($id, $request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Employee updated',
                'data' => $employee,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update employee #$id", ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not update employee',
                'data' => null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $result = $this->biotime->deleteEmployee($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Employee deleted',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to delete employee #$id", ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not delete employee',
                'data' => null,
            ], 500);
        }
    }

    public function timeCardReport(Request $request)
    {
        $data = $this->biotime->getTimeCardReport($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Time card report retrieved',
            'data' => $data
        ]);
    }

    public function monthlyPunchReport(Request $request)
    {
        $data = $this->biotime->getMonthlyPunchReport($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Monthly punch report retrieved',
            'data' => $data
        ]);
    }

    public function attendanceSummary(Request $request)
    {
        $data = $this->biotime->getAttendanceSummary($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Attendance summary retrieved',
            'data' => $data
        ]);
    }

    public function dailyTimeCardReport(Request $request)
    {
        $data = $this->biotime->getDailyTimeCardReport($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Daily time card report retrieved',
            'data' => $data
        ]);
    }

    public function scheduledPunchReport(Request $request)
    {
        $data = $this->biotime->getScheduledPunchReport($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Scheduled punch report retrieved',
            'data' => $data
        ]);
    }
}
