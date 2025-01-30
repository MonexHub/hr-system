<?php

use App\Filament\Employee\Pages\CompleteProfile;
use App\Http\Controllers\Admin\EmployeeAccountSetupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to('/admin/login');
});
use App\Http\Controllers\Admin\EmployeeProfileController;

Route::get('admin/employees/{filament}/profile/download', [EmployeeProfileController::class, 'download'])
    ->name('filament.profile.download')
    ->middleware(['auth']);


Route::middleware(['auth'])->group(function () {
    Route::get('/employee/complete-profile', CompleteProfile::class)
        ->name('filament.employee.pages.complete-profile');
});

Route::get('/employee/setup-account/{token}', [EmployeeAccountSetupController::class, 'showSetupForm'])
    ->name('employee.setup-account');
Route::post('/employee/setup-account', [EmployeeAccountSetupController::class, 'setupAccount'])
    ->name('employee.complete-setup');
