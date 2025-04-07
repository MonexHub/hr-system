<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Announcement::query()->with(['department', 'creator']);

            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->has('is_important')) {
                $query->where('is_important', filter_var($request->is_important, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->has('company_wide')) {
                $isCompanyWide = filter_var($request->company_wide, FILTER_VALIDATE_BOOLEAN);
                $query->whereNull('department_id', $isCompanyWide);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Announcements retrieved successfully',
                'data' => $query->latest()->get()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch announcements: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve announcements',
                'data' => null
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'department_id' => 'nullable|exists:departments,id',
                'icon' => 'nullable|string|max:50',
                'is_important' => 'boolean',
            ]);

            $data['creator_id'] = Auth::id();

            $announcement = Announcement::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Announcement created successfully',
                'data' => $announcement->load('department', 'creator')
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create announcement: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Announcement creation failed',
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $announcement = Announcement::with(['department', 'creator'])->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Announcement retrieved successfully',
                'data' => $announcement
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve announcement with ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Announcement not found',
                'data' => null
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $announcement = Announcement::findOrFail($id);

            $data = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'department_id' => 'nullable|exists:departments,id',
                'icon' => 'nullable|string|max:50',
                'is_important' => 'boolean',
            ]);

            $announcement->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Announcement updated successfully',
                'data' => $announcement->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update announcement with ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Update failed',
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $announcement = Announcement::findOrFail($id);
            $announcement->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Announcement deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to delete announcement with ID $id: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'data' => null
            ], 500);
        }
    }
}
