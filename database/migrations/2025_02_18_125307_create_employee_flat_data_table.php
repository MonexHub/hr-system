<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_flat_data', function (Blueprint $table) {
            $table->id();

            // From departments
            $table->string('department_name');
            $table->string('department_code');
            $table->text('department_description')->nullable();
            $table->string('department_parent_code')->nullable();
            $table->string('department_manager_code')->nullable();
            $table->string('department_organization_unit_code')->nullable();
            $table->boolean('department_is_active')->default(true);
            $table->string('department_phone')->nullable();
            $table->string('department_email')->nullable();
            $table->string('department_location')->nullable();
            $table->decimal('department_annual_budget', 15, 2)->default(0);
            $table->integer('department_current_headcount')->default(0);
            $table->integer('department_max_headcount')->default(0);

            // From job_titles
            $table->string('job_title_name');
            $table->text('job_title_description')->nullable();
            $table->decimal('job_title_net_salary_min', 12, 2)->nullable();
            $table->decimal('job_title_net_salary_max', 12, 2)->nullable();
            $table->boolean('job_title_is_active')->default(true);

            // From employees
            $table->string('user_code')->nullable();
            $table->string('employee_code');
            $table->enum('application_status', ['profile_incomplete', 'active', 'inactive'])->default('profile_incomplete');
            $table->string('unit_code')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('birthdate');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('job_title')->default('unassigned');
            $table->string('branch')->default('unassigned');
            $table->enum('employment_status', ['pending', 'active', 'terminated', 'resigned'])->default('pending');
            $table->date('appointment_date');
            $table->enum('contract_type', ['permanent', 'contract', 'probation', 'undefined'])->default('undefined');
            $table->enum('terms_of_employment', ['full-time', 'part-time', 'temporary'])->nullable();
            $table->date('contract_end_date')->nullable();
            $table->decimal('salary', 10, 2)->default(0);
            $table->string('reporting_to_code')->nullable();


            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_flat_data');
    }
};
