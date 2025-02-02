<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Candidates Table
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->enum('status', [
                'applied',
                'screening',
                'interview',
                'offer',
                'hired',
                'rejected',
                'withdrawn'
            ])->default('applied');
            $table->string('resume_path')->nullable();
            $table->string('photo_path')->nullable();
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->string('salary_currency')->default('USD');
            $table->string('notice_period')->nullable();
            $table->text('summary')->nullable();
            $table->json('skills')->nullable();
            $table->json('education')->nullable();
            $table->json('experience')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('email');
            $table->fullText(['first_name', 'last_name', 'summary']);
        });

        // Job Postings Table
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('position_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('document_path')->nullable();
            $table->boolean('is_document_based')->default(false);
            $table->json('requirements')->nullable();
            $table->json('responsibilities')->nullable();
            $table->string('employment_type');
            $table->string('location')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency')->default('USD');
            $table->boolean('hide_salary')->default(false);
            $table->integer('positions_available')->default(1);
            $table->integer('positions_filled')->default(0);
            $table->date('publishing_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->json('skills_required')->nullable();
            $table->json('education_requirements')->nullable();
            $table->json('experience_requirements')->nullable();
            $table->json('benefits')->nullable();
            $table->json('screening_questions')->nullable();
            $table->integer('minimum_years_experience')->nullable();
            $table->string('education_level')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Job Applications Table
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('job_posting_id')->constrained();
            $table->foreignId('candidate_id')->constrained();
            $table->enum('status', [
                'submitted',
                'under_review',
                'shortlisted',
                'rejected',
                'interview_scheduled',
                'interview_completed',
                'offer_made',
                'offer_accepted',
                'offer_declined',
                'withdrawn',
                'hired'
            ])->default('submitted');
            $table->string('cover_letter_path')->nullable();
            $table->json('additional_documents')->nullable();
            $table->json('screening_answers')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('interview_feedback')->nullable();
            $table->json('assessment_results')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['job_posting_id', 'status']);
            $table->index(['candidate_id', 'status']);
            $table->index('application_number');
        });

        // Interview Schedules
        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained();
            $table->integer('round_number')->default(1);
            $table->foreignId('interviewer_id')->constrained('users');
            $table->datetime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->enum('type', [
                'phone_screening',
                'technical',
                'hr',
                'culture_fit',
                'final'
            ]);
            $table->enum('mode', ['phone', 'video', 'in_person']);
            $table->enum('status', [
                'scheduled',
                'confirmed',
                'completed',
                'cancelled',
                'rescheduled',
                'no_show'
            ])->default('scheduled');
            $table->text('cancellation_reason')->nullable();
            $table->json('interview_questions')->nullable();
            $table->json('feedback')->nullable();
            $table->integer('rating')->nullable();
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('scheduled_at');
            $table->index('status');
            $table->index(['interviewer_id', 'scheduled_at']);
        });

        // Job Offers
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->string('offer_number')->unique();
            $table->foreignId('job_application_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->decimal('base_salary', 15, 2);
            $table->string('salary_currency')->default('USD');
            $table->json('benefits_package')->nullable();
            $table->json('additional_allowances')->nullable();
            $table->date('proposed_start_date');
            $table->text('additional_terms')->nullable();
            $table->text('special_conditions')->nullable();
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'sent',
                'accepted',
                'negotiating',
                'rejected',
                'expired'
            ])->default('draft');
            $table->date('valid_until');
            $table->datetime('sent_at')->nullable();
            $table->datetime('responded_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('negotiation_history')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('offer_number');
            $table->index('valid_until');
            $table->index(['status', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_offers');
        Schema::dropIfExists('interview_schedules');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_postings');
        Schema::dropIfExists('candidates');
    }
};
