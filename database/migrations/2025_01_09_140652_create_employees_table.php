<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Core Tables

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->enum('application_status', ['profile_incomplete', 'active', 'inactive'])->default('profile_incomplete');
            $table->foreignId('unit_id')->nullable()->constrained('organization_units')->nullOnDelete();

            // Basic Information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('birthdate');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])
                ->nullable();
            $table->string('profile_photo')->nullable();

            // Contact Information
            $table->string('phone_number')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();

            // Employment Details
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('job_title')->default('unassigned');
            $table->string('branch')->default('unassigned');
            $table->enum('employment_status', ['pending', 'active', 'terminated', 'resigned'])->default('pending');
            $table->date('appointment_date');
            $table->enum('contract_type', ['permanent', 'contract', 'probation', 'undefined'])->default('undefined');
            $table->enum('terms_of_employment', ['full-time', 'part-time', 'temporary'])->nullable();
            $table->date('contract_end_date')->nullable();
            $table->decimal('salary', 10, 2)->default(0);
            $table->unsignedBigInteger('reporting_to')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('reporting_to')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();
        });

        // Counties Table
        Schema::create('counties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        // Documents & Uploads
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['id_proof', 'resume', 'certificate', 'other']);
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Emergency Contacts
        Schema::create('employee_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship');
            $table->string('phone')->nullable();
            $table->string('alternative_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Education & Qualifications
        Schema::create('employee_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('institution');
            $table->string('degree');
            $table->string('field_of_study');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('grade', 4, 2)->nullable();
            $table->text('achievements')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('issuing_organization');
            $table->string('credential_id')->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('has_expiry')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Work History
        Schema::create('employee_work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('position');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('responsibilities')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_contact')->nullable();
            $table->text('achievements')->nullable();
            $table->string('reason_for_leaving')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Skills & Training
        Schema::create('employee_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('skill_name');
            $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert']);
            $table->enum('category', ['technical', 'soft_skills', 'languages', 'other']);
            $table->text('description')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('provider');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('certification_received')->nullable();
            $table->text('description')->nullable();
            $table->enum('training_type', ['internal', 'external', 'online', 'workshop']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_trainings');
        Schema::dropIfExists('employee_skills');
        Schema::dropIfExists('employee_work_experiences');
        Schema::dropIfExists('employee_certifications');
        Schema::dropIfExists('employee_education');
        Schema::dropIfExists('employee_emergency_contacts');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
    }
};
