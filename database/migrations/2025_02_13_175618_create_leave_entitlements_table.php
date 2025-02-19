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
        Schema::dropIfExists('leave_entitlements');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('leave_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->foreignId('employee_id')->constrained('employees');
            $table->integer('entitled_days');
            $table->date('valid_from');
            $table->date('valid_to');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Using a shorter name for the unique constraint
            $table->unique(
                ['leave_type_id', 'employee_id', 'valid_from', 'valid_to'],
                'le_type_emp_validity_unique'
            );
        });
    }
};
