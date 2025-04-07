<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use Illuminate\Support\Facades\Log;

class LeaveController extends Controller
{
    public function index($id)
    {
        $requests = LeaveRequest::where('employee_id', $id)->get();
        return response([
            'status' => 'success',
            'message' => 'Leave Requests',
            'data' => $requests
        ]);
    }

    public function getLeave($id)
    {
        $leave = LeaveRequest::find($id);
        if ($leave) {
            return response([
                'status' => 'success',
                'message' => 'Leave Detail',
                'data' => $leave
            ]);
        } else {
            Log::error("Leave not found with ID: $id");
            return response([
                'status' => 'failed',
                'message' => 'Leave not found',
                'data' => null
            ], 403);
        }
    }

    public function leavetypes()
    {
        $leaves = LeaveType::all();
        return response([
            'status' => 'success',
            'message' => 'Successfully retrieved Leave types',
            'data' => $leaves
        ]);
    }

    public function getLeaveType($id)
    {
        $leave = LeaveType::find($id);
        if ($leave) {
            return response([
                'status' => 'success',
                'message' => 'Leave Type Detail',
                'data' => $leave
            ]);
        } else {
            Log::error("Leave type not found with ID: $id");
            return response([
                'status' => 'failed',
                'message' => 'Leave Type not found',
                'data' => null
            ], 403);
        }
    }

    public function requestLeave(Request $request)
    {
        try {
            $data = $request->validate([
                'employee_id' => 'required',
                'leave_type_id' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'reason' => 'required',
                'total_days'=> 'required',
            ]);

            $leave = LeaveRequest::create($data);
            return response([
                'status' => 'success',
                'message' => 'Leave Requested submitted successfully. Pending Approval',
                'data' => $leave
            ]);
        } catch (\Exception $e) {
            Log::error('Leave request failed: ' . $e->getMessage());
            return response([
                'status' => 'failed',
                'message' => 'Leave request failed',
                'data' => null
            ], 500);
        }
    }

    public function getLeaveBalance($id)
    {
        $balance = LeaveBalance::where('employee_id', $id)->with(['employee','leaveType','creator'])->get();
        return response([
            'status' => 'success',
            'message' => 'Successfully retrieved leave balances',
            'data' => $balance
        ]);
    }
}
