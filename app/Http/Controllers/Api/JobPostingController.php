<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JobPostingController extends Controller
{

    public function index()
    {
        try {
            $jobs = JobPosting::with(['applications.applicant'])->get();

            return response()->json([
                'status' => 'success',
                'message' => 'All job posts with applicants retrieved successfully.',
                'data' => $jobs
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve job posts with applicants: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve job posts with applicants.',
                'data' => null
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'department_id' => 'nullable|exists:departments,id',
                'employment_type' => 'required|string',
                'location' => 'required|string',
                'is_remote' => 'boolean',
                'salary_min' => 'nullable|numeric',
                'salary_max' => 'nullable|numeric',
                'salary_currency' => 'nullable|string|max:10',
                'hide_salary' => 'boolean',
                'positions_available' => 'required|integer|min:1',
                'publishing_date' => 'nullable|date',
                'closing_date' => 'nullable|date|after_or_equal:publishing_date',
                'is_featured' => 'boolean',
                'requirements' => 'nullable|array',
                'responsibilities' => 'nullable|array',
                'skills_required' => 'nullable|array',
                'education_requirements' => 'nullable|array',
                'experience_requirements' => 'nullable|array',
                'benefits' => 'nullable|array',
                'screening_questions' => 'nullable|array',
                'minimum_years_experience' => 'nullable|integer',
                'education_level' => 'nullable|string',
                'is_document_based' => 'boolean',
                'document_path' => 'nullable|string'
            ]);

            $job = JobPosting::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Job posting created successfully.',
                'data' => $job
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create job posting: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to create job posting.',
                'data' => null
            ], 500);
        }
    }

    public function candidates($jobPostingId)
    {
        try {
            $job = JobPosting::with(['applications.applicant'])->findOrFail($jobPostingId);

            return response()->json([
                'status' => 'success',
                'message' => 'Candidates retrieved successfully.',
                'data' => $job->applications
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve candidates for job ID $jobPostingId: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not retrieve candidates.',
                'data' => null
            ], 500);
        }
    }

    public function scheduleInterview(Request $request, $applicationId)
    {
        try {
            $data = $request->validate([
                'interview_date' => 'required|date',
                'interview_notes' => 'nullable|string'
            ]);

            $application = JobApplication::findOrFail($applicationId);
            $application->update([
                'status' => 'interview_scheduled',
                'interview_date' => $data['interview_date'],
                'interview_notes' => $data['interview_notes'] ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Interview scheduled successfully.',
                'data' => $application
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to schedule interview: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to schedule interview.',
                'data' => null
            ], 500);
        }
    }

    public function hireCandidate($applicationId)
    {
        try {
            $application = JobApplication::findOrFail($applicationId);
            $application->update([
                'status' => 'hired',
                'hired_at' => now()
            ]);

            $job = $application->jobPosting;
            $job->increment('positions_filled');

            if ($job->positions_filled >= $job->positions_available) {
                $job->markAsFilled();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Candidate hired successfully.',
                'data' => $application
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to hire candidate: " . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Hiring failed.',
                'data' => null
            ], 500);
        }
    }
}
