<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_imports', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique()->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('birthdate')->nullable();
            $table->enum('contract_type', ['permanent', 'contract'])->nullable();
            $table->date('appointment_date')->nullable();
            $table->string('job_title')->nullable();
            $table->string('branch')->nullable();
            $table->string('department')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('email')->nullable();

            // Processing status fields
            $table->enum('import_status', ['pending', 'processed', 'failed'])->default('pending');
            $table->text('import_errors')->nullable();
            $table->timestamp('processed_at')->nullable();

            // Tracking fields
            $table->string('batch_id')->nullable();
            $table->integer('row_number')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('import_status');
            $table->index('batch_id');
            $table->index('employee_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_imports');
    }
};
