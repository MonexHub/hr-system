<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckProfileCompletion
{
    public function handle(Request $request, Closure $next)
    {
        // Bypass routes
        $bypassRoutes = [
            'filament.employee.auth.login',
            'filament.employee.auth.logout',
            'filament.employee.auth.registration',
        ];

        // If not authenticated or on bypass routes, proceed
        if (!Auth::check() || $request->routeIs($bypassRoutes)) {
            return $next($request);
        }

        $user = Auth::user();

        // Safe routes
        $safeRoutes = [
            'filament.employee.pages.complete-profile',
            'filament.employee.pages.dashboard',
            'filament.employee.auth.logout'
        ];

        // Allow safe routes
        if ($request->routeIs($safeRoutes)) {
            return $next($request);
        }

        // Check if employee record exists
        if (!$user->employee) {
            // Split name if possible
            $nameParts = explode(' ', $user->name, 2);
            $firstName = $nameParts[0] ?? $user->name;
            $lastName = $nameParts[1] ?? '';

            try {
                $employee = $user->employee()->create([
                    'user_id' => $user->id,
                    'employee_code' => 'EMP-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'application_status' => 'profile_incomplete',
                    'birthdate' => now()->subYears(18), // Default to 18 years ago
                    'gender' => 'other', // Default gender
                    'contract_type' => 'undefined', // Default contract type
                    'appointment_date' => now(), // Current date as appointment date
                    'job_title' => 'unassigned', // Default job title
                    'branch' => 'unassigned', // Default branch
                    'salary' => 0, // Default salary
                    'employment_status' => 'pending', // Default employment status
                ]);

                return redirect()->route('filament.employee.pages.complete-profile');
            } catch (\Exception $e) {
                Log::error('Failed to create employee record: ' . $e->getMessage());

                // Optionally, you can add more specific error handling
                return redirect()->route('filament.employee.auth.login')
                    ->with('error', 'Unable to create employee profile. Please contact support.');
            }
        }



        // Check application status
        if ($user->employee->application_status === 'profile_incomplete') {
            return redirect()->route('filament.employee.pages.complete-profile');
        }

        return $next($request);
    }
}
