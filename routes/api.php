<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\OrganizationController;


Route::post('/login',[AuthController::class,'login']);


Route::middleware(['auth:api'])->group(function () {

    //Auth Routes
    Route::get('/logout',[AuthController::class,'logout']);


    //Employee Routes

    Route::prefix('employee')->group(function () {


    Route::get('/all',[EmployeeController::class,'index']);
    Route::get('/{id}',[EmployeeController::class,'getEmployee']);
    Route::put('/edit',[EmployeeController::class,'editEmployeeDetails']);

    //Employee Training Routes
    Route::get('/training',[EmployeeController::class,'getEmployeeTraining']);
    Route::get('/training/{id}',[EmployeeController::class,'getEmployeeTrainingDetails']);
    });



        //Leave Routes
    Route::prefix('leave')->group(function () {
        Route::get('/all/{id}',[LeaveController::class,'index']);
        Route::get('/{id}',[LeaveController::class,'getLeave']);
        Route::get('/type',[LeaveController::class,'leavetypes']);
        Route::get('/type/{id}',[LeaveController::class,'getLeaveType']);
        Route::post('/request',[LeaveController::class,'requestLeave']);
    });








    //Attendance Routes
    Route::get('/attendance',[AttendanceController::class,'index']);
    Route::get('/attendances',[AttendanceController::class,'getAttendance']);
    Route::get('/attendance/{id}',[AttendanceController::class,'getUserAttendanceDetails']);


    //Organization Routes
    Route::get('/departments',[OrganizationController::class,'departments']);
    Route::get('/jobtitles',[OrganizationController::class,'jobTitles']);




});
