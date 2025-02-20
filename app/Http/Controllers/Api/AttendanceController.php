<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index() {
        $attendances = Attendance::where('employee_id', Auth::user()->employee->id)->get();
        return response(['message' => 'Attendance List',
    'data' => $attendances]);

    }


    public function getAttendance(){
        $attendance = Attendance::all();
        if($attendance){
            return response(['message' => 'Attendance List',
        'data' => $attendance]);
        }else{
            return response(['message' => 'Attendance not found'],403);
        }
    }

    public function getUserAttendanceDetails($id){
        $attendance = Attendance::where('employee_id', $id)->get();
        if($attendance){
            return response(['message' => 'Attendance Details',
        'data' => $attendance]);
        }else{
            return response(['message' => 'Attendance not found'],403);
        }
    }
}
