<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->integer('entitled_days');
            $table->integer('carried_forward_days')->default(0);
            $table->integer('additional_days')->default(0);
            $table->integer('taken_days')->default(0);
            $table->integer('pending_days')->default(0);
            // Virtual column for days_remaining calculation
            $table->integer('days_remaining')->virtualAs('entitled_days + carried_forward_days + additional_days - taken_days - pending_days')->nullable();
            $table->year('year');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['employee_id', 'leave_type_id', 'year']);
            $table->index('days_remaining');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_balances');
    }
};
