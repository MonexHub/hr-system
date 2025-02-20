<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeTraining;

class EmployeeController extends Controller
{
    public function index(){


        $employees = Employee::all();

        return response(['message' => 'Employee List',
    'data' => $employees]);
    }

    public function getEmployee($id){

        $employee = Employee::find($id);

        if($employee){
            return response(['message' => 'Employee Detail',
        'data' => $employee]);
        }else{
            return response(['message' => 'Employee not found']);
        }
    }


    public function editEmployeeDetails($request) {

        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',
            'department_id' => 'required',
            'job_title_id' => 'required',
            'salary' => 'required',
            'joining_date' => 'required',
            'status' => 'required'
        ]);

        $employee = Employee::where('id', Auth::user()->employee->id)->first();

        $employee->name = $validatedData['name'];
        $employee->email = $validatedData['email'];
        $employee->phone = $validatedData['phone'];
        $employee->address = $validatedData['address'];
        $employee->department_id = $validatedData['department_id'];
        $employee->job_title_id = $validatedData['job_title_id'];
        $employee->salary = $validatedData['salary'];
        $employee->joining_date = $validatedData['joining_date'];
        $employee->status = $validatedData['status'];

        $employee->save();

        return response(['message' => 'Employee Details Updated',
    'data' => $employee]);



    }


    public function getEmployeeTraining(){

        $employee = EmployeeTraining::all();

        if($employee){
            return response(['message' => 'Employee Training',
        'data' => $employee]);
        }else{
            return response(['message' => 'Employee not found'],402);
        }
    }

    public function getEmployeeTrainingDetails($id){

        $employee = EmployeeTraining::where('employee_id', $id)->get();

        if($employee){
            return response(['message' => 'Employee Training Details',
        'data' => $employee]);
        }else{
            return response(['message' => 'Employee not found'],403);
        }
    }
}
