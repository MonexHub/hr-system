<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\JobPosting;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicJobController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::orderBy('name')->get();

        $jobs = JobPosting::published()
            ->with('department')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('department'), function ($query) use ($request) {
                $query->where('department_id', $request->department);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('employment_type', $request->type);
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

        // Get experience and nationality options from Candidate model
        $experienceOptions = Candidate::getYearsOfExperienceOptions();
        $nationalityOptions = Candidate::getNationalityOptions();

        return view('jobs.apply', compact('jobPosting', 'experienceOptions', 'nationalityOptions'));
    }

    public function store(Request $request, JobPosting $jobPosting)
    {
        Log::info('Application submission started', [
            'job_posting_id' => $jobPosting->id,
            'request_data' => $request->except(['resume', 'cover_letter'])
        ]);

        try {
            // Validate the request
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'nationality' => 'required|string|in:' . implode(',', array_keys(Candidate::getNationalityOptions())),
                'current_job_title' => 'required|string|max:255',
                'years_of_experience' => 'required|string|in:' . implode(',', array_keys(Candidate::getYearsOfExperienceOptions())),
                'resume' => 'required|file|mimes:pdf,doc,docx|max:5120',
                'cover_letter' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                'portfolio_url' => 'nullable|url|max:255',
                'linkedin_url' => 'nullable|url|max:255',
                'expected_salary' => 'nullable|numeric|min:0',
                'notice_period_days' => 'nullable|integer|min:0|max:180',
                'professional_summary' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();

            // Step 1: Create or update candidate first
            $candidate = Candidate::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'phone' => $validated['phone'],
                    'nationality' => $validated['nationality'],
                    'current_job_title' => $validated['current_job_title'],
                    'years_of_experience' => $validated['years_of_experience'],
                    'expected_salary' => $validated['expected_salary'] ?? null,
                    'notice_period_days' => $validated['notice_period_days'] ?? null,
                    'professional_summary' => $validated['professional_summary'] ?? null,
                    'status' => Candidate::STATUS_APPLIED
                ]
            );

            Log::info('Candidate created/updated', ['candidate_id' => $candidate->id]);

            // Step 2: Create job application
            $application = new JobApplication([
                'job_posting_id' => $jobPosting->id,
                'application_number' => 'APP-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'status' => 'submitted',
            ]);

            // Associate with candidate
            $application->candidate_id = $candidate->id;

            // Handle cover letter if provided
            if ($request->hasFile('cover_letter')) {
                $coverLetterPath = $request->file('cover_letter')->store('applications/cover-letters', 'public');
                $application->cover_letter_path = $coverLetterPath;
            }

            // Save additional documents if any
            if ($request->has('additional_documents')) {
                $application->additional_documents = json_encode([
                    'portfolio_url' => $validated['portfolio_url'] ?? null,
                    'linkedin_url' => $validated['linkedin_url'] ?? null,
                    'professional_summary' => $validated['professional_summary'] ?? null
                ]);
            }

            $application->save();

            // Handle resume upload for candidate
            if ($request->hasFile('resume')) {
                $resumePath = $request->file('resume')->store('candidates/resumes', 'public');
                $candidate->update(['resume_path' => $resumePath]);
            }

            DB::commit();
            Log::info('Application submission completed successfully');

            return redirect()->route('jobs.thank-you')
                ->with('success', 'Your application has been submitted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Application submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up any uploaded files
            if (isset($resumePath)) {
                Storage::disk('public')->delete($resumePath);
            }
            if (isset($coverLetterPath)) {
                Storage::disk('public')->delete($coverLetterPath);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'There was an error submitting your application: ' . $e->getMessage()]);
        }
    }



    public function thankYou(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application
    {
        Log::info('Thank you page accessed');

        if (!view()->exists('jobs.thank-you')) {
            Log::error('Thank you view does not exist');
            abort(404);
        }

        return view('jobs.thank-you');
    }
}
