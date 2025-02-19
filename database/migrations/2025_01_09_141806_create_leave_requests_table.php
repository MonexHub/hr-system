<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->string('attachment_path')->nullable();

            // Status tracking
            $table->enum('status', [
                'pending',
                'department_approved',
                'hr_approved',
                'ceo_review',
                'approved',
                'rejected',
                'cancelled'
            ])->default('pending');

            // Department Head Approval
            $table->foreignId('department_approved_by')->nullable()->constrained('users');
            $table->timestamp('department_approved_at')->nullable();
            $table->text('department_remarks')->nullable();

            // HR Approval
            $table->foreignId('hr_approved_by')->nullable()->constrained('users');
            $table->timestamp('hr_approved_at')->nullable();
            $table->text('hr_remarks')->nullable();

            // CEO Approval (if needed)
            $table->foreignId('ceo_approved_by')->nullable()->constrained('users');
            $table->timestamp('ceo_approved_at')->nullable();
            $table->text('ceo_remarks')->nullable();

            // Rejection/Cancellation
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Status and employee related indexes
            $table->index('status', 'lr_status_idx');
            $table->index(['employee_id', 'status'], 'lr_emp_status_idx');
            $table->index(['leave_type_id', 'status'], 'lr_type_status_idx');

            // Date related indexes
            $table->index('start_date', 'lr_start_date_idx');
            $table->index('end_date', 'lr_end_date_idx');
            $table->index(['start_date', 'end_date'], 'lr_date_range_idx');

            // Approval related indexes
            $table->index(['department_approved_by', 'department_approved_at'], 'lr_dept_approval_idx');
            $table->index(['hr_approved_by', 'hr_approved_at'], 'lr_hr_approval_idx');
            $table->index(['ceo_approved_by', 'ceo_approved_at'], 'lr_ceo_approval_idx');

            // Request number index
            $table->index('request_number', 'lr_number_idx');

            // Composite indexes for common queries
            $table->index(['employee_id', 'leave_type_id', 'status'], 'lr_emp_type_status_idx');
            $table->index(['status', 'start_date', 'end_date'], 'lr_status_date_range_idx');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
