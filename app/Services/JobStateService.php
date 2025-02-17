<?php

namespace App\Services;

use App\Models\{JobOffer, Employee};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobStateService
{
    public function handleOfferAcceptance(JobOffer $jobOffer): void
    {
        try {
            DB::transaction(function () use ($jobOffer) {
                $candidate = $jobOffer->jobApplication->candidate;
                $jobPosting = $jobOffer->jobApplication->jobPosting;

                Log::info('Creating employee record', [
                    'candidate' => $candidate->toArray(),
                    'job_posting' => $jobPosting->toArray()
                ]);

                Employee::create([
                    'employee_code' => Employee::generateEmployeeCode(),
                    'first_name' => $candidate->first_name,
                    'middle_name' => $candidate->middle_name ?? null,
                    'last_name' => $candidate->last_name,
                    'email' => $candidate->email,
                    'phone_number' => $candidate->phone ?? null,
                    'gender' => $candidate->gender ?? 'male',
                    'birthdate' => $candidate->date_of_birth ?? now(),
                    'job_title_id' => $jobPosting->job_title_id,
                    'department_id' => $jobPosting->department_id,
                    'appointment_date' => $jobOffer->proposed_start_date,
                    'salary' => $jobOffer->base_salary,
                    'employment_status' => 'probation',
                    'application_status' => 'active',
                    'contract_type' => 'permanent',
                    'terms_of_employment' => $jobOffer->additional_terms ?? null
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to create employee record', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
