<?php

namespace App\Http\Controllers\Api;

use App\Models\NotificationPreference;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class NotificationPreferenceController extends Controller
{
    // Display a listing of the notification preferences.
    public function index()
    {
        try {
            $user = Auth::user();
            $query = NotificationPreference::query();

            if ($user->hasRole(['super_admin', 'hr-manager'])) {
                $preferences = $query->with('employee')->get();
            } else {
                $preferences = $query->where('employee_id', $user->employee->id)->with('employee')->get();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Notification preferences retrieved successfully',
                'data' => $preferences
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch notification preferences: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve notification preferences',
                'data' => null
            ], 500);
        }
    }

    // Show the form for creating a new notification preference.
    public function create()
    {
        try {
            $user = Auth::user();
            $employees = [];

            if ($user->hasRole(['super_admin', 'hr-manager'])) {
                $employees = Employee::doesntHave('notificationPreferences')
                    ->where('employment_status', 'active')
                    ->pluck('full_name', 'id');
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Employee list retrieved',
                'data' => $employees
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch employees: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve employee list',
                'data' => null
            ], 500);
        }
    }

    // Store a newly created notification preference in storage.
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user->hasRole(['super_admin', 'hr-manager']);

            $validatedData = $request->validate([
                'employee_id' => $isAdmin ? 'required|exists:employees,id' : 'nullable',
                'preferred_language' => 'required|in:en,sw',
                'holiday_notifications' => 'boolean',
                'birthday_notifications' => 'boolean',
                'email_notifications' => 'boolean',
                'in_app_notifications' => 'boolean',
            ]);

            if (!$isAdmin) {
                $validatedData['employee_id'] = $user->employee->id;
            }

            $preference = NotificationPreference::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification Preference created successfully',
                'data' => $preference
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification preference: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not create notification preference',
                'data' => null
            ], 500);
        }
    }

    // Display the specified notification preference.
    public function show(NotificationPreference $notificationPreference)
    {
        try {
            $this->authorize('view', $notificationPreference);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification Preference retrieved',
                'data' => $notificationPreference
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve notification preference: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve notification preference',
                'data' => null
            ], 403);
        }
    }

    // Show the form for editing the specified notification preference.
    public function edit(NotificationPreference $notificationPreference)
    {
        try {
            $this->authorize('update', $notificationPreference);

            $employees = [];
            $user = Auth::user();

            if ($user->hasRole(['super_admin', 'hr-manager'])) {
                $employees = Employee::pluck('full_name', 'id');
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Employee list retrieved',
                'data' => $employees
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to prepare edit form: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not prepare edit data',
                'data' => null
            ], 500);
        }
    }

    // Update the specified notification preference in storage.
    public function update(Request $request, NotificationPreference $notificationPreference)
    {
        try {
            $this->authorize('update', $notificationPreference);

            $validatedData = $request->validate([
                'preferred_language' => 'required|in:en,sw',
                'holiday_notifications' => 'boolean',
                'birthday_notifications' => 'boolean',
                'email_notifications' => 'boolean',
                'in_app_notifications' => 'boolean',
            ]);

            $notificationPreference->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification Preference updated successfully',
                'data' => $notificationPreference
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update notification preference: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not update notification preference',
                'data' => null
            ], 500);
        }
    }

    // Remove the specified notification preference from storage.
    public function destroy(NotificationPreference $notificationPreference)
    {
        try {
            $this->authorize('delete', $notificationPreference);
            $notificationPreference->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification Preference deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete notification preference: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not delete notification preference',
                'data' => null
            ], 500);
        }
    }
}
