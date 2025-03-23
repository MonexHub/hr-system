<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\PerfomanceAppraisalController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\AnnouncementController;



Route::post('/login',[AuthController::class,'login']);


Route::middleware(['auth:api'])->group(function () {

    //Auth Routes
    Route::get('/logout',[AuthController::class,'logout']);

    Route::prefix('user')->group(function () {
        Route::get('', [AuthController::class, 'me']);
        Route::put('', [AuthController::class, 'updateProfile']);
        Route::post('/upload-photo', [AuthController::class, 'uploadPhoto']);
    });


    //Employee Routes

    Route::prefix('employees')->group(function () {



        Route::get('', [EmployeeController::class, 'index']);
        Route::post('', [EmployeeController::class, 'store']);
        Route::get('/{id}', [EmployeeController::class, 'show']);
        Route::put('/{id}', [EmployeeController::class, 'update']);
        Route::delete('/{id}', [EmployeeController::class, 'destroy']);

        Route::post('/{id}/create-account', [EmployeeController::class, 'createUserAccount']);
        Route::post('/{id}/resend-setup-link', [EmployeeController::class, 'resendSetupLink']);



    Route::get('/{id}',[EmployeeController::class,'getEmployee']);
    Route::put('/edit',[EmployeeController::class,'editEmployeeDetails']);

    //Employee Training Routes
    Route::get('/training',[EmployeeController::class,'getEmployeeTraining']);
    Route::get('/training/{id}',[EmployeeController::class,'getEmployeeTrainingDetails']);


    //Employee Import Routes

                   // CSV file upload + background import
            Route::post('/import', [EmployeeController::class, 'import']);
                        // CSV sample download
            Route::get('/import/sample', [EmployeeController::class, 'downloadSample']);
    });



        //Leave Routes
    Route::prefix('leave')->group(function () {
        Route::get('/all/{id}',[LeaveController::class,'index']);
        Route::get('/{id}',[LeaveController::class,'getLeave']);
        Route::get('/type',[LeaveController::class,'leavetypes']);
        Route::get('/type/{id}',[LeaveController::class,'getLeaveType']);
        Route::post('/request',[LeaveController::class,'requestLeave']);
    });

    //Perfomance Appraisal Routes
    Route::prefix('appraisals')->group(function () {
        Route::get('', [PerfomanceAppraisalController::class, 'index']);
        Route::post('', [PerfomanceAppraisalController::class, 'store']);
        Route::get('/{id}', [PerfomanceAppraisalController::class, 'show']);
        Route::put('/{id}', [PerfomanceAppraisalController::class, 'update']);
        Route::delete('/{id}', [PerfomanceAppraisalController::class, 'destroy']);
        Route::post('/{id}/restore', [PerfomanceAppraisalController::class, 'restore']);
        Route::post('/{id}/submit', [PerfomanceAppraisalController::class, 'submit']);
        Route::post('/{id}/supervisor-approve', [PerfomanceAppraisalController::class, 'supervisorApprove']);
        Route::post('/{id}/hr-approve', [PerfomanceAppraisalController::class, 'hrApprove']);
    });


    //Holiday Routes
    Route::prefix('holidays')->group(function () {
        Route::get('', [HolidayController::class, 'index']);
        Route::post('', [HolidayController::class, 'store']);
        Route::get('/{id}', [HolidayController::class, 'show']);
        Route::put('/{id}', [HolidayController::class, 'update']);
        Route::delete('/{id}', [HolidayController::class, 'destroy']);
    });

    //Announcement Routes
    Route::prefix('announcement')->group(function () {
        Route::get('', [AnnouncementController::class, 'index']);
        Route::post('', [AnnouncementController::class, 'store']);
        Route::get('/{id}', [AnnouncementController::class, 'show']);
        Route::put('/{id}', [AnnouncementController::class, 'update']);
        Route::delete('/{id}', [AnnouncementController::class, 'destroy']);
    });








    //Attendance Routes
    Route::get('/attendance',[AttendanceController::class,'index']);
    Route::get('/attendances',[AttendanceController::class,'getAttendance']);
    Route::get('/attendance/{id}',[AttendanceController::class,'getUserAttendanceDetails']);


    //Organization Routes
    Route::get('/departments',[OrganizationController::class,'departments']);
    Route::get('/jobtitles',[OrganizationController::class,'jobTitles']);




});
