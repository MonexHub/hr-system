<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Holiday::query();

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('is_recurring')) {
                $query->where('is_recurring', filter_var($request->is_recurring, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->has('send_notification')) {
                $query->where('send_notification', filter_var($request->send_notification, FILTER_VALIDATE_BOOLEAN));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Holidays retrieved successfully',
                'data' => $query->latest('date')->get()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch holidays: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not fetch holidays',
                'data' => null
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'name_sw' => 'required|string|max:255',
                'description' => 'nullable|string',
                'description_sw' => 'nullable|string',
                'date' => 'required|date',
                'is_recurring' => 'boolean',
                'type' => 'required|in:public,religious,company',
                'status' => 'required|in:active,inactive',
                'send_notification' => 'boolean',
            ]);

            $holiday = Holiday::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Holiday created successfully',
                'data' => $holiday
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create holiday: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not create holiday',
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $holiday = Holiday::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Holiday details',
                'data' => $holiday
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve holiday with ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve holiday',
                'data' => null
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $holiday = Holiday::findOrFail($id);

            $data = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'name_sw' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'description_sw' => 'nullable|string',
                'date' => 'sometimes|required|date',
                'is_recurring' => 'boolean',
                'type' => 'in:public,religious,company',
                'status' => 'in:active,inactive',
                'send_notification' => 'boolean',
            ]);

            $holiday->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Holiday updated successfully',
                'data' => $holiday
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update holiday with ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not update holiday',
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $holiday = Holiday::findOrFail($id);
            $holiday->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Holiday deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to delete holiday with ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not delete holiday',
                'data' => null
            ], 500);
        }
    }

    public function getBirthdays()
    {
        try {
            $birthdays = Employee::with('jobTitle') // eager load job title
                ->orderByRaw('DAY(birthdate) DESC')
                ->get(['id', 'first_name', 'last_name', 'birthdate', 'job_title_id'])
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'description' => optional($employee->jobTitle)->name, // use job title as description
                        'date' => $employee->birthdate,
                        'type' => 'birthday',
                        'status' => 'active',
                        'is_today' => date('m-d') === date('m-d', strtotime($employee->birthdate)),
                        'formatted_date' => Carbon::parse($employee->birthdate)->format('F j'),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Birthdays retrieved successfully',
                'data' => $birthdays
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch birthdays: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not fetch birthdays',
                'data' => null
            ], 500);
        }
    }
}
