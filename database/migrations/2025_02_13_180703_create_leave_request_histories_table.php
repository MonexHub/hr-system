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
        Schema::create('leave_request_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests');
            $table->string('action'); // e.g., 'created', 'department_approved', 'hr_approved', etc.
            $table->string('status_from');
            $table->string('status_to');
            $table->text('remarks')->nullable();
            $table->foreignId('acted_by')->constrained('users');
            $table->timestamps();

            $table->index('action');
            $table->index(['leave_request_id', 'action']);
            $table->index('acted_by');
            $table->index(['status_from', 'status_to']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_histories');
    }
};
