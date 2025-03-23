<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;



class AuthController extends Controller
{


    public function login(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate the user
        if (!Auth::attempt($validatedData)) {
            return response(['message' => 'Invalid credentials'], 401);
        }

    // Retrieve the authenticated user
    $user = Auth::user();

    $employee = $user->employee()->with([
        'department:id,name',
        'jobTitle:id,name',
        'skills',
        'education',
        'documents',
        'emergencyContacts',
        'dependents',
        'financials'
    ])->first();

    // Revoke all existing tokens for the user
// Revoke all existing tokens for the user
DB::table('oauth_access_tokens')
    ->where('user_id', $user->id)
    ->update(['revoked' => true]);

    // Create a new access token for the user
    $accessToken = $user->createToken('authToken')->accessToken;

    // Load the 'employee' relationship and retrieve role names
    $user->load('employee');
    $roles = $user->getRoleNames(); // Returns a collection of role names
    $permissions = $user->getAllPermissions()->pluck('name'); // Returns a collection of permission names

    $employee['roles'] = $roles;
    $employee['permissions'] = $permissions;

    // Return the custom user data along with the access token
    return response([
        'user' => $employee,
        'access_token' => $accessToken,
    ]);
    }


        // GET /api/me
        public function me()
        {
            $user = Auth::user();

            $employee = $user->employee()->with([
                'department:id,name',
                'jobTitle:id,name',
                'skills',
                'education',
                'documents',
                'emergencyContacts',
                'dependents',
                'financials',
            ])->first();

            if (!$employee) {
                return response()->json(['message' => 'Employee profile not found.'], 404);
            }

            return response()->json($employee);
        }

        // PUT /api/me
        public function updateProfile(Request $request)
        {
            $user = Auth::user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json(['message' => 'Employee profile not found.'], 404);
            }

            $validated = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'gender' => ['sometimes', Rule::in(['male', 'female', 'other'])],
                'marital_status' => ['sometimes', Rule::in(['single', 'married', 'divorced', 'widowed'])],
                'phone_number' => 'sometimes|string',
                'email' => [
                    'sometimes', 'email',
                    Rule::unique('employees')->ignore($employee->id),
                ],
                'permanent_address' => 'sometimes|string',
                'city' => 'sometimes|string',
                'postal_code' => 'nullable|string',
            ]);

            $employee->update($validated);

            return response()->json([
                'message' => 'Profile updated successfully.',
                'employee' => $employee->fresh(),
            ]);
        }

        // POST /api/me/upload-photo
        public function uploadPhoto(Request $request)
        {
            $request->validate([
                'profile_photo' => 'required|image|max:2048',
            ]);

            $employee = Auth::user()->employee;

            if (!$employee) {
                return response()->json(['message' => 'Employee profile not found.'], 404);
            }

            $path = $request->file('profile_photo')->store('employee-photos', 'public');

            $employee->profile_photo = $path;
            $employee->save();

            return response()->json([
                'message' => 'Profile photo updated.',
                'profile_photo_url' => asset('storage/' . $path),
            ]);
        }




    public function logout(Request $request){
        $request->user()->token()->revoke();
        return response(['message' => 'Successfully logged out']);
    }


}
