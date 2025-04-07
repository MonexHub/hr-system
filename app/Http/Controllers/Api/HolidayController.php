<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Holiday;
use Illuminate\Support\Facades\Log;

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
}
