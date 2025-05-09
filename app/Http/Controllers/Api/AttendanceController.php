<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Services\ZKBiotimeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index()
    {
        try {

            // Check if the user is authenticated
            $attendances = Attendance::where('employee_id', Auth::user()->employee->id)->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Attendance List',
                'data' => $attendances
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch attendance list: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not fetch attendance list',
                'data' => null
            ], 500);
        }
    }

    public function getAttendance()
    {
        try {
            $attendance = Attendance::all();
            return response()->json([
                'status' => 'success',
                'message' => 'Attendance List',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch all attendance: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve attendance',
                'data' => null
            ], 500);
        }
    }

    public function getUserAttendanceDetails($id)
    {
        try {
            $attendance = Attendance::where('employee_id', $id)->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Attendance Details',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to fetch attendance details for employee ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve attendance details',
                'data' => null
            ], 500);
        }
    }

    public function getTodayAttendanceData()
    {
        try {
            $employee = Auth::user()->employee;
            if (!$employee) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Employee not found',
                    'data' => null
                ], 404);
            }

            if ($employee->external_employee_id == null) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'External employee ID not found',
                    'data' => null
                ], 404);
            }

            $bioService = new ZKBiotimeService();
            $employeeData = $bioService->getEmployees(['emp_code' => $employee->external_employee_id]);

            //from the array of employees, get the employee with the same external_employee_id
            $matchedEmployee = collect($employeeData['data'] ?? [])
                ->firstWhere('emp_code', $employee->external_employee_id);

            $employeeId = $matchedEmployee['id'] ?? null;

            if (!$employeeId) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Employee ID not found in attendance data',
                    'data' => null
                ], 404);
            }

            $todayData = $bioService->getDailyTimeCardReport(
                [
                    'employees' => $employeeId
                ]
            );

            if (!$todayData) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No attendance data found for today',
                    'data' => null
                ], 404);
            } else {
                $todayData = $todayData['data'] ?? [];

                if (empty($todayData)) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'No attendance data found for today',
                        'data' => null
                    ], 404);
                }
                // Map the data to the desired format
                $todayData = collect($todayData)->map(function ($item) {
                    return [
                        'check_in' => $item['clock_in'] ?? null,
                        'check_out' => $item['clock_out'] ?? null,
                        'total_hours'  => $item['total_hrs'] ?? null,
                    ];
                })->first();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Today\'s Attendance Data',
                    'data' => $todayData
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch today\'s attendance status: ' . $e->getMessage() . '\nAt Line:' . $e->getLine());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve today\'s attendance status',
                'data' => null
            ], 500);
        }
    }
}
