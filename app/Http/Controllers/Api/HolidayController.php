<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Holiday;



class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        // Apply filters if present
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

        return response()->json($query->latest('date')->get());
    }

    public function store(Request $request)
    {
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

        return response()->json($holiday, 201);
    }

    public function show($id)
    {
        $holiday = Holiday::findOrFail($id);
        return response()->json($holiday);
    }

    public function update(Request $request, $id)
    {
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

        return response()->json($holiday);
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return response()->json(['message' => 'Holiday deleted']);
    }
}
