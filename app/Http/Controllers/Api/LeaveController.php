<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;

class LeaveController extends Controller
{
   public function index($id){
    $requests = LeaveRequest::where('employee_id', $id)->get();
    return response(['message' => 'Leave Requests',
'data' => $requests]);
    }

    public function getLeave($id){
        $leave = LeaveRequest::find($id);
        if($leave){
            return response(['message' => 'Leave Detail',
        'data' => $leave]);
        }else{
            return response(['message' => 'Leave not found'],403);
        }
    }

    public function leavetypes(){
        $leaves = LeaveType::all();
        return response(['message' => 'Leave Type List',
    'data' => $leaves]);
    }

    public function getLeaveType($id){
        $leave = LeaveType::find($id);
        if($leave){
            return response(['message' => 'Leave Type Detail',
        'data' => $leave]);
        }else{
            return response(['message' => 'Leave Type not found'],403);
        }
    }

    public function requestLeave($requests) {

        $data = request()->validate([
            'employee_id' => 'required',
            'leave_type_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'reason' => 'required'
        ]);

        $leave = LeaveRequest::create($data);
        return response(['message' => 'Leave Requested',
    'data' => $leave]);

    }


    public function getLeaveBalance($id) {

        $balance = LeaveBalance::where('employee_id', $id)->get();
        return response(['message' => 'Leave Balance',
    'data' => $balance]);



    }


}
