<?php

namespace App\Http\Controllers\Api;

use App\Models\NotificationPreference;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationPreferenceController extends Controller
{
    // Display a listing of the notification preferences.
    public function index()
    {
        $user = Auth::user();
        $query = NotificationPreference::query();

        if ($user->hasRole(['super_admin', 'hr-manager'])) {
            $preferences = $query->with('employee')->get();
        } else {
            $preferences = $query->where('employee_id', $user->employee->id)->with('employee')->get();
        }

        return view('notification_preferences.index', compact('preferences'));
    }

    // Show the form for creating a new notification preference.
    public function create()
    {
        $user = Auth::user();
        $employees = [];

        if ($user->hasRole(['super_admin', 'hr-manager'])) {
            $employees = Employee::doesntHave('notificationPreferences')
                ->where('employment_status', 'active')
                ->pluck('full_name', 'id');
        }

        return view('notification_preferences.create', compact('employees'));
    }

    // Store a newly created notification preference in storage.
    public function store(Request $request)
    {
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

        NotificationPreference::create($validatedData);

        return redirect()->route('notification-preferences.index')
            ->with('success', 'Notification Preference created successfully.');
    }

    // Display the specified notification preference.
    public function show(NotificationPreference $notificationPreference)
    {
        $this->authorize('view', $notificationPreference);

        return view('notification_preferences.show', compact('notificationPreference'));
    }

    // Show the form for editing the specified notification preference.
    public function edit(NotificationPreference $notificationPreference)
    {
        $this->authorize('update', $notificationPreference);

        $employees = [];
        $user = Auth::user();

        if ($user->hasRole(['super_admin', 'hr-manager'])) {
            $employees = Employee::pluck('full_name', 'id');
        }

        return view('notification_preferences.edit', compact('notificationPreference', 'employees'));
    }

    // Update the specified notification preference in storage.
    public function update(Request $request, NotificationPreference $notificationPreference)
    {
        $this->authorize('update', $notificationPreference);

        $validatedData = $request->validate([
            'preferred_language' => 'required|in:en,sw',
            'holiday_notifications' => 'boolean',
            'birthday_notifications' => 'boolean',
            'email_notifications' => 'boolean',
            'in_app_notifications' => 'boolean',
        ]);

        $notificationPreference->update($validatedData);

        return redirect()->route('notification-preferences.index')
            ->with('success', 'Notification Preference updated successfully.');
    }

    // Remove the specified notification preference from storage.
    public function destroy(NotificationPreference $notificationPreference)
    {
        $this->authorize('delete', $notificationPreference);

        $notificationPreference->delete();

        return redirect()->route('notification-preferences.index')
            ->with('success', 'Notification Preference deleted successfully.');
    }
}
