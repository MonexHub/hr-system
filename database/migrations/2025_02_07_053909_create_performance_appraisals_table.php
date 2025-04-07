<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_appraisals', function (Blueprint $table) {
            $table->id();

            // Employee Relations
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('immediate_supervisor_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('hr_id')->nullable()->constrained('employees')->onDelete('set null');

            // Evaluation Period
            $table->date('evaluation_period_start');
            $table->date('evaluation_period_end');
            $table->date('evaluation_date')->nullable();

            // Performance Metrics (1-5 scale)
            $table->integer('quality_of_work')->comment('1-5 rating')->nullable();
            $table->integer('productivity')->comment('1-5 rating')->nullable();
            $table->integer('job_knowledge')->comment('1-5 rating')->nullable();
            $table->integer('reliability')->comment('1-5 rating')->nullable();
            $table->integer('communication')->comment('1-5 rating')->nullable();
            $table->integer('teamwork')->comment('1-5 rating')->nullable();

            // Comments and Feedback
            $table->text('achievements')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('supervisor_comments')->nullable();
            $table->text('hr_comments')->nullable();
            $table->text('employee_comments')->nullable();

            // Overall Rating and Category
            $table->decimal('overall_rating', 3, 2)->nullable();
            $table->string('performance_category')->nullable();

            // Status Management
            $table->enum('status', [
                'draft',
                'submitted',
                'supervisor_approved',
                'hr_approved',
                'completed'
            ])->default('draft');

            // Workflow Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->timestamp('hr_approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Standard Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_appraisals');
    }
};
