<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;


class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::query()->with(['department', 'creator']);

        // Optional Filters
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

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'icon' => 'nullable|string|max:50',
            'is_important' => 'boolean',
        ]);

        $data['creator_id'] = Auth::id(); // Assuming relationship exists

        $announcement = Announcement::create($data);

        return response()->json($announcement->load('department', 'creator'), 201);
    }

    public function show($id)
    {
        $announcement = Announcement::with(['department', 'creator'])->findOrFail($id);
        return response()->json($announcement);
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'department_id' => 'nullable|exists:departments,id',
            'icon' => 'nullable|string|max:50',
            'is_important' => 'boolean',
        ]);

        $announcement->update($data);

        return response()->json($announcement->fresh());
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted']);
    }
}
