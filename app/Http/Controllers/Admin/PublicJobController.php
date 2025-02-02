<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\JobPosting;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicJobController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')->get();

        $jobs = JobPosting::published()
            ->with('department')
            ->when(request('search'), function ($query) {
                $query->where('title', 'like', '%' . request('search') . '%');
            })
            ->when(request('department'), function ($query) {
                $query->where('department_id', request('department'));
            })
            ->when(request('type'), function ($query) {
                $query->where('employment_type', request('type'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('jobs.index', compact('jobs', 'departments'));
    }


    public function show(JobPosting $jobPosting)
    {
        abort_if(!$jobPosting->isOpen(), 404);

        return view('jobs.show', compact('jobPosting'));
    }

    public function apply(JobPosting $jobPosting)
    {
        abort_if(!$jobPosting->isOpen(), 404);

        return view('jobs.apply', compact('jobPosting'));
    }

    public function store(Request $request, JobPosting $jobPosting)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'current_position' => 'required|string|max:255',
            'current_company' => 'required|string|max:255',
            'experience_years' => 'required|numeric|min:0|max:50',
            'education_level' => 'required|string',
            'resume' => 'required|file|mimes:pdf|max:5120',
            'cover_letter' => 'nullable|file|mimes:pdf|max:5120',
            'portfolio_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'expected_salary' => 'nullable|numeric',
            'notice_period' => 'nullable|string|max:50',
            'additional_notes' => 'nullable|string|max:1000',
            'referral_source' => 'nullable|string|max:255',
        ]);

        // Handle file uploads
        $resumePath = $request->file('resume')->store('applications/resumes', 'public');
        $coverLetterPath = null;
        if ($request->hasFile('cover_letter')) {
            $coverLetterPath = $request->file('cover_letter')->store('applications/cover-letters', 'public');
        }

        // Create application
        $application = JobApplication::create([
            'job_posting_id' => $jobPosting->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'current_position' => $validated['current_position'],
            'current_company' => $validated['current_company'],
            'experience_years' => $validated['experience_years'],
            'education_level' => $validated['education_level'],
            'resume_path' => $resumePath,
            'cover_letter_path' => $coverLetterPath,
            'portfolio_url' => $validated['portfolio_url'],
            'linkedin_url' => $validated['linkedin_url'],
            'expected_salary' => $validated['expected_salary'],
            'notice_period' => $validated['notice_period'],
            'additional_notes' => $validated['additional_notes'],
            'referral_source' => $validated['referral_source'],
            'status' => 'new'
        ]);

        // Send notifications if needed
        // event(new JobApplicationSubmitted($application));

        return redirect()->route('jobs.thank-you')
            ->with('success', 'Your application has been submitted successfully!');
    }

    public function thankYou()
    {
        return view('jobs.thank-you');
    }
}
