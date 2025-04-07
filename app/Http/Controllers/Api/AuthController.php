<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;
use App\Services\BeemService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'fcm_token' => 'sometimes|string',
            ]);

            if (!Auth::attempt($validatedData)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid credentials',
                    'data' => null
                ], 401);
            }

            $user = Auth::user();
            if (isset($request->fcm_token)) {
                $user = User::find($user->id);
                $user->update([
                    'fcm_token' => $request->fcm_token,
                ]);
            }

            $employee = $user->employee()->with([
                'department:id,name',
                'jobTitle:id,name',
                'skills',
                'education',
                'documents',
                'emergencyContacts',
                'dependents',
                'financials',
                'notificationPreferences'
            ])->first();

            DB::table('oauth_access_tokens')
                ->where('user_id', $user->id)
                ->update(['revoked' => true]);

            $accessToken = $user->createToken('authToken')->accessToken;

            $user->load('employee');
            $roles = $user->getRoleNames();
            $permissions = $user->getAllPermissions()->pluck('name');

            $employee['roles'] = $roles;
            $employee['permissions'] = $permissions;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => $employee,
                    'access_token' => $accessToken,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Login failed',
                'data' => null
            ], 500);
        }
    }

    public function me()
    {
        try {
            $user = Auth::user();

            $user->load('employee');


            $employee = $user->employee()->with([
                'department:id,name',
                'jobTitle:id,name',
                'skills',
                'education',
                'documents',
                'emergencyContacts',
                'dependents',
                'financials',
                'notificationPreferences'
            ])->first();

            $roles = $user->getRoleNames();
            $permissions = $user->getAllPermissions()->pluck('name');

            $employee['roles'] = $roles;
            $employee['permissions'] = $permissions;

            if (!$employee) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Employee profile not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Employee profile retrieved',
                'data' => $employee
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve profile: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve profile',
                'data' => null
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Employee profile not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'gender' => ['sometimes', Rule::in(['male', 'female', 'other'])],
                'marital_status' => ['sometimes', Rule::in(['single', 'married', 'divorced', 'widowed'])],
                'phone_number' => 'sometimes|string',
                'email' => [
                    'sometimes',
                    'email',
                    Rule::unique('employees')->ignore($employee->id),
                ],
                'permanent_address' => 'sometimes|string',
                'city' => 'sometimes|string',
                'postal_code' => 'nullable|string',
                'profile_photo' => 'sometimes|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('profile_photo')) {
                Log::info('Profile photo detected.');

                if ($request->file('profile_photo')->isValid()) {
                    $path = $request->file('profile_photo')->store('user-photos', 'public');
                    $validated['profile_photo'] = $path;
                    Log::info('Profile photo uploaded to: ' . $path);
                } else {
                    Log::warning('Uploaded profile photo is not valid.');
                }
            } else {
                Log::info('No profile photo was uploaded.');
            }

            $validated['profile_photo'] = Storage::url($path);

            $employee->update($validated);



            $user->update([
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'avatar_url' => $validated['profile_photo']
            ]);

            //Send a notification to the user
            try {
                if ($user->fcm_token) {
                    $firebase = new FirebaseNotificationService();
                    $firebase->send([
                        'token' => $user->fcm_token,
                        'title' => 'Profile Update',
                        'body' => 'You have successfully updated your profile.',
                        'data' => ['type' => 'profile_update']
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send verification notification: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $employee->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not update profile',
                'data' => null
            ], 500);
        }
    }

    public function uploadPhoto(Request $request)
    {
        try {
            $request->validate([
                'profile_photo' => 'required|image|max:2048',
            ]);

            $employee = Auth::user()->employee;

            if (!$employee) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Employee profile not found',
                    'data' => null
                ], 404);
            }

            if ($request->hasFile('profile_photo')) {
                Log::info('Profile photo detected.');

                if ($request->file('profile_photo')->isValid()) {
                    $path = $request->file('profile_photo')->store('user-photos', 'public');
                    $validated['profile_photo'] = $path;
                    Log::info('Profile photo uploaded to: ' . $path);
                } else {
                    Log::warning('Uploaded profile photo is not valid.');
                }
            } else {
                Log::info('No profile photo was uploaded.');
            }

            $path = Storage::url($path);

            $user = Auth::user();
            $user = User::find($user->id);
            $user->avatar_url = $path;
            $user->save();


            $employee->profile_photo = $path;
            $employee->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile photo updated',
                'data' => ['profile_photo_url' => asset('storage/' . $path)]
            ]);
        } catch (\Exception $e) {
            Log::error('Photo upload failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not upload photo',
                'data' => null
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            //Send a notification to the user
            try {
                $user = Auth::user();
                if ($user->fcm_token) {
                    $firebase = new FirebaseNotificationService();
                    $firebase->send([
                        'token' => $user->fcm_token,
                        'title' => 'Account Activity',
                        'body' => 'You have successfully logged out from your account.',
                        'data' => ['type' => 'logout']
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send verification notification: ' . $e->getMessage());
            }
            $request->user()->token()->revoke();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Logout failed',
                'data' => null
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string'
        ]);

        $identifier = $request->input('identifier');

        $user = Employee::where('email', $request->identifier)
            ->orWhere('phone_number', $request->identifier)
            ->first();

        $userData =  User::where('id', $user->user_id)->first();

        if (!$user && !$userData) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Employee not found',
                'data' => null
            ], 404);
        }

        $otp = rand(1000, 9999);

        try {
            $mailtrap = MailtrapClient::initSendingEmails(apiKey: env('MAILTRAP_API_KEY'));

            $email = (new MailtrapEmail())
                ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
                ->to(new Address($user->email, $user->first_name . ' ' . $user->last_name))
                ->subject('Welcome to ' . config('app.name') . ' - Verify to continue')
                ->text('Your Account Reset OTP is: ' . $otp);

            $mailtrap->send($email);
        } catch (\Throwable $th) {
            Log::error('Failed to send email: ' . $th->getMessage());
        }
        try {
            $beem = new BeemService();
            $beem->sendSMS($user->phone_number, 'Welcome to ' . config('app.name') . ". Your Account Reset OTP is: " . $otp);
        } catch (\Throwable $th) {
            Log::error('Failed to send SMS: ' . $th->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent to registered email and phone number',
            'data' => bcrypt($otp)
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Employee::where('email', $request->identifier)
            ->orWhere('phone_number', $request->identifier)
            ->first();



        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Employee not found',
                'data' => null
            ], 404);
        }

        $user = User::where('id', $user->user_id)->first();

        $user->password = Hash::make($request->password);
        $user->save();

        //Send a notification to the user
        try {
            if ($user->fcm_token) {
                $firebase = new FirebaseNotificationService();
                $firebase->send([
                    'token' => $user->fcm_token,
                    'title' => 'Profile Update',
                    'body' => 'You have successfully changed your password.',
                    'data' => ['type' => 'password_reset']
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send verification notification: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Password has been reset successfully',
            'data' => null
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Current password is incorrect',
                'data' => null
            ], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully',
            'data' => null
        ]);
    }
}
