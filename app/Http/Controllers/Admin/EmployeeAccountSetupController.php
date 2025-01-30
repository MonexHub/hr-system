<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EmployeeAccountSetupController extends Controller
{
    public function showSetupForm(Request $request, string $token)
    {
        $email = $request->query('email');

        // Find employee by email
        $employee = Employee::where('email', $email)->first();

        if (!$employee) {
            return redirect()->to('/admin/login')
                ->with('error', 'Invalid setup link.');
        }

        // Verify token
        $cacheKey = 'account_setup_' . $employee->id;
        if (!Cache::has($cacheKey) || Cache::get($cacheKey) !== $token) {
            return redirect()->to('/admin/login')
                ->with('error', 'Invalid or expired setup link. Please contact HR.');
        }

        return view('auth.employee-setup', compact('token', 'email'));
    }

    public function setupAccount(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'name.required' => 'The username field is required.',
            'name.unique' => 'This username is already taken.',
        ]);

        // Find employee
        $employee = Employee::where('email', $validated['email'])->firstOrFail();

        // Verify token
        $cacheKey = 'account_setup_' . $employee->id;
        if (!Cache::has($cacheKey) || Cache::get($cacheKey) !== $validated['token']) {
            return back()->with('error', 'Invalid or expired setup link. Please contact HR.');
        }

        // Find existing user
        $user = User::findOrFail($employee->user_id);

        // Update user with new credentials
        $user->update([
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        // Remove setup token
        Cache::forget($cacheKey);

        // Redirect to Filament login page
        return redirect()->to('/admin/login')
            ->with('success', 'Account setup completed successfully. You can now log in with your username and password.');
    }
}
