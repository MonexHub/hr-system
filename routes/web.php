<?php

use App\Filament\Employee\Pages\CompleteProfile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\Admin\EmployeeProfileController;

Route::get('admin/employees/{filament}/profile/download', [EmployeeProfileController::class, 'download'])
    ->name('filament.profile.download')
    ->middleware(['auth']);


Route::middleware(['auth'])->group(function () {
    Route::get('/employee/complete-profile', CompleteProfile::class)
        ->name('filament.employee.pages.complete-profile');
});
