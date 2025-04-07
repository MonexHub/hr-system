<?php

use App\Filament\Employee\Pages\CompleteProfile;
use App\Http\Controllers\Admin\EmployeeAccountSetupController;
use App\Http\Controllers\Admin\PublicJobController;
use App\Livewire\JobApplication;
use App\Livewire\JobListing;
use App\Livewire\JobShow;
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

Route::prefix('jobs')->name('jobs.')->group(function () {
    // Put the thank you route BEFORE any routes with parameters
    Route::get('thank-you', [PublicJobController::class, 'thankYou'])->name('thank-you');

    // Then put all other routes
    Route::get('/', [PublicJobController::class, 'index'])->name('index');
    Route::get('/{jobPosting}/apply', [PublicJobController::class, 'apply'])->name('apply');
    Route::post('/{jobPosting}/apply', [PublicJobController::class, 'store'])->name('store');
    Route::get('/{jobPosting}', [PublicJobController::class, 'show'])->name('show');
});

Route::get('/employee/resume/{employee}', [\App\Http\Controllers\EmployeeResumeController::class, 'show'])
    ->name('employee.resume')
    ->middleware(['auth']);
