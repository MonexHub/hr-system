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
        Schema::create('leave_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('approver_id')->constrained('users');
            $table->enum('level', ['department_head', 'hr', 'ceo']);
            $table->boolean('is_active')->default(true);
            $table->boolean('can_approve_all_departments')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['department_id', 'approver_id', 'level']);

            $table->index('level');
            $table->index('is_active');
            $table->index(['department_id', 'level', 'is_active']);
            $table->index(['approver_id', 'level', 'is_active']);
            $table->index('can_approve_all_departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_approvers');
    }
};
