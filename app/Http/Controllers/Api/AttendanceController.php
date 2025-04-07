<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index() {
        try {
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

    public function getAttendance() {
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

    public function getUserAttendanceDetails($id) {
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
}
