<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\JobTitle;
use Illuminate\Support\Facades\Log;

class OrganizationController extends Controller
{
    public function departments()
    {
        try {
            $organizations = Department::all();
            return response()->json([
                'status' => 'success',
                'message' => 'Department List',
                'data' => $organizations
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch departments: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve departments',
                'data' => null
            ], 500);
        }
    }


    public function jobTitles()
    {
        try {
            $jobTitles = JobTitle::all();
            return response()->json([
                'status' => 'success',
                'message' => 'Job Title List',
                'data' => $jobTitles
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch job titles: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve job titles',
                'data' => null
            ], 500);
        }
    }
}
