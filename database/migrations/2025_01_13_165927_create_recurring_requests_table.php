<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_requests', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            // Basic Information
            $table->string('reference_number')->unique();
            $table->string('request_type'); // recruitment, promotion, transfer, training, etc.
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['draft', 'submitted', 'in_review', 'approved', 'rejected', 'completed'])
                ->default('draft');

            // Dates
            $table->date('target_date');
            $table->date('completion_date')->nullable();

            // Request Details
            $table->enum('category', ['personal', 'professional', 'organizational']);
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->enum('impact_level', ['individual', 'team', 'department', 'organization']);

            // JSON Fields for Arrays
            $table->json('skills_required')->nullable();
            $table->json('benefits')->nullable();
            $table->json('risks')->nullable();
            $table->json('alternatives_considered')->nullable();
            $table->json('resource_requirements')->nullable();
            $table->json('success_criteria')->nullable();
            $table->json('evaluation_metrics')->nullable();

            // Additional Fields
            $table->text('rejection_reason')->nullable();
            $table->string('attachment_path')->nullable();

            // Timestamps and Soft Deletes
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('reference_number');
            $table->index('status');
            $table->index('request_type');
            $table->index('target_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_requests');
    }
};
